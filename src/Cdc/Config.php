<?php namespace Cdc;

use \Nette\Utils\Arrays as A,
    \Nette\Http\Session,
    \Nette\Http\RequestFactory,
    \Nette\Http\Response,
    \Hautelook\Phpass\PasswordHash,
    \Nette\Security\User,
    \Nette\Http\UserStorage,
    \C as C,
    \Nette\Security\Permission;

/**
 * This would also work as simple global variables, but I need IDE autocomplete
 */
abstract class Config 
{

    use \Nette\SmartObject;

    /**
     * Array [template path, module name]
     *
     * @var array
     */
    public static $layoutTemplate = ['layout/default.phtml', ''];
    public static $dateFormat = 'd/m/Y';
    public static $dateTimeFormat = 'd/m/Y H:i:s';
    public static $sender;
    public static $pg_fts_regconfig = 'portuguese';
    private static $definition_cache = array();

    /**
     *
     * @param type $definition_name
     * @return \Cdc\Definition
     */
    public static function getDefinition($definition_name, $operation = DEFAULT_OPERATION)
    {
        if (!array_key_exists($definition_name, self::$definition_cache)) {
            $definition = new $definition_name($operation);
            self::$definition_cache[$definition_name] = $definition;
        } else {
            self::$definition_cache[$definition_name]->setOperation($operation);
        }

        return self::$definition_cache[$definition_name];
    }

    /**
     *
     * @var \Nette\Security\IAuthenticator
     */
    public static $authenticatorClass;

    /**
     * Storage for javascript buffers
     * @var string
     */
    public static $scripts;

    /**
     * Skins to be "compiled"
     * @var array [[index => path], ...]
     */
    public static $skins;

    /**
     * Debug mode
     * @var bool
     */
    public static $debug = false;

    /**
     *
     * @var \Composer\Autoload\ClassLoader
     */
    public static $loader;

    /**
     *
     * @var \Nette\Http\SessionSection
     */
    public static $session;

    /**
     *
     * @var type \Nette\Http\Session
     */
    public static $sessionContainer;

    /**
     *
     * @var \Cdc\Router
     */
    public static $router;

    /**
     *
     * @var \Knp\Menu\MenuFactory
     */
    public static $menuFactory;

    /**
     *  array($callback, array($menu_name, $controller, $menu, &$options)),
     */
    public static $changeMenuCallback = false;

    /**
     *
     * @var \Nette\Caching\Storages\FileStorage
     */
    public static $cache;

    /**
     *
     * @var \Nette\Security\User
     */
    public static $user;

    /**
     * @var \Nette\Http\Request
     */
    public static $request;

    /**
     *
     * @var \Nette\Http\Response
     */
    public static $response;

    /**
     *
     * @var \Nette\Security\IAuthenticator
     */
    public static $auth;

    /**
     * Mailer configuration
     * @var array
     */
    public static $mailer;

    /**
     *
     * @var \Cdc\Route
     */
    public static $matchedRoute;

    /**
     * Temp dir
     * @var string
     */
    public static $tmp;
    public static $upload;
    public static $upload_abs;
    public static $root;
    public static $root_abs;
    public static $base;

    /**
     * Database configuration
     * @var array
     */
    public static $db;
    public static $default_db;
    public static $timezone;
    public static $modules;
    public static $resources;

    /**
     *
     * @var \Cdc\Dispatcher
     */
    public static $dispatcher;
    public static $labels;

    /**
     *
     * @var \Hautelook\Phpass\PasswordHash
     */
    public static $hasher;
    public static $acl = array();
    public static $default_login_controller = '\Duke\Controller\Login';
    public static $title = 'Default Title';

    private function __construct()
    {

    }

    public static function preSource($settings, $application, $defaultSettings)
    {

    }

    public static function postSource($settings, $application, $defaultSettings)
    {

    }

    public static function source($settings, $application = null, $defaultSettings = array())
    {

        C::preSource($settings, $application, $defaultSettings);

        C::setEnvironment($settings, $application, $defaultSettings);

        C::registerDatabaseConnections();

        C::addModuleLoaders();

        C::loadModuleResources();

        C::initializeUtils();

        C::configureRouter();


        self::$hasher = new PasswordHash(10, false);

        date_default_timezone_set(C::$timezone);


        C::postSource($settings, $application, $defaultSettings);
    }

    protected static function initializeUtils()
    {
        $requestFactory = new RequestFactory;
        C::$request = $requestFactory->createHttpRequest();

        C::$root = C::$request->url->getBasePath();
        C::$upload = C::$root . C::$upload . '/';

        C::$response = new Response;

        C::$menuFactory = new \Knp\Menu\MenuFactory;
    }

    public static function startSessionFor($sessionName)
    {

        $session = new Session(C::$request, C::$response);
        $session->start();
        self::$sessionContainer = $session;
        self::$session = $session->getSection($sessionName);
    }

    protected static function setEnvironment($settings, $defaultSettings = array())
    {

        $settings = A::mergeTree($settings, $defaultSettings);

        foreach ($settings as $key => $value) {
            if (!property_exists('C', $key)) {
                throw new \Cdc\Exception\Config\UndocumentedSetting('Please define and document the setting "' . $key . '" in your C class.');
            }
            C::$$key = $value;
        }
    }

    protected static function registerDatabaseConnections()
    {
        foreach (C::$db as $key => $value) {
            $dsn = A::get($value, 'dsn');
            $user = A::get($value, 'user', null);
            $pass = A::get($value, 'password', null);

            $defaults = array(
                \PDO::ATTR_CASE => \PDO::CASE_LOWER,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            );

            $custom = A::get($value, 'options', array());

            $options = A::mergeTree($custom, $defaults);

            \Cdc\Pdo\Pool::register($key, $dsn, $user, $pass, $options);
        }
    }

    /**
     * @return \PDO
     */
    public static function connection()
    {
        if (C::$debug) {
            $mode = \Cdc\Pdo\Pool::DEBUG;
        } else {
            $mode = \Cdc\Pdo\Pool::NORMAL;
        }
        return \Cdc\Pdo\Pool::getConnection(C::$default_db, $mode);
    }

    /**
     *
     * @return \PDO
     */
    public static function pdo()
    {
        self::connection();
        return self::$pdo;
    }

    protected static function addModuleLoaders()
    {
        foreach (C::$modules as $k => $v) {
            C::$loader->add($k, $v);
            $conf = $v . DIRECTORY_SEPARATOR . 'boot.php';
            if (file_exists($conf)) {
                include $conf;
            }
        }
    }

    public static function exec()
    {
        $content = null;
        $controller = null;


        if (C::$matchedRoute) {

            $target = C::$matchedRoute->getTarget();

            if (array_key_exists('login_controller', $target)) {
                $loginController = $target['login_controller'];
            } else {
                $loginController = C::$default_login_controller;
            }

            C::startSessionFor($loginController);

            C::$auth = $authenticator = new self::$authenticatorClass;
            $storage = new UserStorage(C::$sessionContainer);
            $storage->setNamespace($loginController);
            $authorizator = new Permission;

            C::$user = new User($storage, $authenticator, $authorizator);


            list($controller, $action, $parameters) = C::$dispatcher->dispatch();

            if (!C::routeAllowed(C::$matchedRoute)) {
                if (!C::$user->isLoggedIn()) {
                    C::$matchedRoute->setTarget(array('class' => $loginController));
                    C::$dispatcher->setMatchedRoute(C::$matchedRoute);
                    list($controller, $action, $parameters) = C::$dispatcher->dispatch();
                } else {
                    C::$response->redirect(C::$dispatcher->router->generate('forbidden'));
                    die;
                }
            }

            $content = call_user_func_array(array($controller, $action), $parameters);
        }
        return array($controller, $content);
    }

    public static function configureRouter()
    {
        $router = self::$router = new \Cdc\Router;
        $router->setBasePath(C::$root);
        foreach (C::$modules as $key => $value) {
            if ($key) {
                $key .= '\\';
            }

            $routes = '\\' . $key . 'Metadata\Routes';

            if (class_exists($routes)) {
                call_user_func(array($routes, 'setup'), $router);
            }
        }

        $u = C::$request->getUrl();

        C::$matchedRoute = $route = $router->match($u->getPath());

        C::$dispatcher = new \Cdc\Dispatcher($router, $route);
    }

    protected static function loadModuleResources()
    {
        foreach (C::$modules as $key => $value) {
            if ($key) {
                $key .= '\\';
            }
            $resources = '\\' . $key . 'Metadata\Resources';
            if (class_exists($resources)) {
                call_user_func(array($resources, 'setup'));
            }
        }
    }

    public static function getResources($roles)
    {
        if (!$roles) {
            return array();
        }
        $sql = new \Cdc\Sql\Select(C::connection());
        try {
            $result = $sql->cols(array('grupo_id', 'permissao_id'))->from(array('permissao'))->where(array('grupo_id in' => $roles))->stmt()->fetchAll();
            return $result;
        } catch (\Exception $e) {
// just keep it quiet
            return array();
        }
    }

    public static function createAcl()
    {
        if (!C::$acl && C::$user) {

            $auth = C::$user->getAuthorizator();

            if (C::$session->acl) {
                C::$acl = C::$session->acl;
                return;
            }
            if (C::$user->isLoggedIn()) {
                $roles = C::$user->getRoles();
                $resources = C::getResources($roles);
            } else {
                $roles = $resources = array();
            }

            foreach ($roles as $role) {
                try {
                    $auth->addRole((string) $role);
                } catch (\Exception $e) {
// really don't care
                }
            }

            C::$acl = array();
            foreach ($resources as $resource) {
                try {
                    $auth->addResource($resource['permissao_id']);
                } catch (\Exception $e) {
// sure
                }
                $auth->allow((string) $resource['grupo_id'], $resource['permissao_id']);
                C::$acl[$resource['permissao_id']] = true;
            }

            C::$session->acl = C::$acl;
        }
    }

    public static function resourceAllowed($resource, $skip = false)
    {
        C::createAcl();
        if ($skip) {
            return true;
        }
        if (!is_array($resource)) {
            $resource = array($resource);
        }

        foreach ($resource as $value) {
            if (isset(C::$acl[$value])) {
                return true;
            }
        }

        return false;
    }

    public static function routeAllowed(\Cdc\Route $route, $skip_check = false)
    {
        C::createAcl();

        if ($skip_check) {
            return true;
        }

        $target = $route->getTarget();

        if (!array_key_exists('resource', $target)) {
            return false; // A pessoa tem que colocar explicitamente que a rota é aberta, usando o valor "none"
        }

        if ($target['resource'] == 'none') { // Recurso aberto
            return true;
        }

        if ($target['resource'] == 'authenticated') {
// Apenas para usuários autenticados
            return C::$user->isLoggedIn();
        }


        if ($target['resource'] == 'auto') {
            $parameters = $route->getParameters();
            $target['resource'] = $parameters['r'];
        }
        if (is_array($target['resource'])) {
            $ok = false;
            foreach ($target['resource'] as $r) {
                $ok = array_key_exists($r, self::$acl);

                if ($ok) {
                    return true;
                }
            }
            return $ok;
        }
        return array_key_exists($target['resource'], self::$acl);
    }
}
