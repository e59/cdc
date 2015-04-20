<?php

namespace Cdc\Controller;

use \C as C;
use \Cdc\Definition;

class Login extends \Cdc\Controller {

    public $loginDestination = 'home';
    public $logoutDestination = 'home';
    public $index = 'login';
    public $module = '';

    /**
     * If this is false, no layout file will be used (i.e. request ends here)
     * @var string
     */
    public $template = false;

    public function process() {
        $post = C::$request->getPost();

        $identity = f($post, C::$auth->getUserColumn());
        $password = f($post, C::$auth->getPasswordColumn());

        try {
            C::$user->login($identity, $password);
            C::createAcl();
            $this->afterLogin();
            $this->redirect($this->loginDestination, array($this->welcomeMessage(), LOG_SUCCESS));
        } catch (\Nette\Security\AuthenticationException $e) {
            event($this->incorrectLoginMessage($e), LOG_ERR);
        }
    }

    public function welcomeMessage() {
        return sprintf('Bem-vindo, %s', C::$user->identity->{C::$auth->getUserColumn()});
    }

    public function incorrectLoginMessage(\Nette\Security\AuthenticationException $e) {
        return 'Dados inválidos.';
    }

    public function logoutMessage() {
        return 'Você foi desconectado.';
    }

    public function afterLogin() {

    }

    public function getForm() {
        $def = array(
            'email' => array(
                \Cdc\Definition::TYPE_WIDGET => array(
                    'widget' => 'email',
                    'attributes' => array(
                        'autofocus' => 'autofocus',
                        'required' => 'required',
                    ),
                ),
            ),
            'senha' => array(
                \Cdc\Definition::TYPE_WIDGET => array(
                    'widget' => 'password',
                    'attributes' => array(
                        'required' => 'required',
                    ),
                ),
            ),
            'remember_me' => array(
                \Cdc\Definition::TYPE_WIDGET => array(
                    'widget' => 'boolean',
                ),
            ),
        );

        $extraOptions = $this->manipulateFormDefinition($def);

        if (!$extraOptions) {
            $extraOptions = (array) $extraOptions;
        }

        $options = array_merge(array(
            'legend' => label('login_form'),
            'controller' => $this,
                ), $extraOptions);

        $formClass = $this->getFormClass();

        $form = new $formClass($def, $options, C::$request->getPost());

        return $form;
    }

    public function indexAction() {

        if (C::$user->isLoggedIn()) {
            $this->redirect($this->loginDestination);
        }

        if (C::$request->isMethod('POST')) {
            $this->process();
        }

        C::$response->setCode(\Nette\Http\Response::S403_FORBIDDEN);

        return $this->getForm()->render($this->getTemplate($this->template));
    }

    public function logoutAction() {
        C::$user->logout(true);
        C::$sessionContainer->destroy();
        $this->redirect($this->logoutDestination, array($this->logoutMessage(), LOG_SUCCESS));
    }

    public function manipulateFormDefinition(&$def) {

    }

}
