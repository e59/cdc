<?php

namespace Cdc;

use \C as C;
use \Nette\Utils\Arrays as A;

abstract class Controller {

    public $formClass = '\Cdc\Form';

    public $module;

    private $definitionCache = array();

    public $js = null;

    public function addJs($content) {
        $this->js .= $content;
    }

    /**
     * @return \Cdc\Definition
     */
    public function getDefinition($class) {
        if (!array_key_exists($class, $this->definitionCache)) {
            $this->definitionCache[$class] = new $class;
        }
        return $this->definitionCache[$class];
    }

    public function link($route, $data = array(), $query_params = array()) {
        return C::$dispatcher->router->generate($route, $data, $query_params);
    }

    public function routeCallback($route, $data = array(), $query_params = array()) {
        return $this->link($route, $data, $query_params);
    }

    public function getFormClass() {
        return '\Cdc\Form';
    }

    public function getTemplate($file, $module = null) {

        if (!$file) {
            return $file;
        }

        if (!$module) {
            $module = $this->module;
        }

        if (!$module) {
            $pieces = explode('\\', get_class($this));
            $module = $pieces[0];
        }

        if (array_key_exists($module, C::$modules)) {
            $fn = C::$modules[$module] . $module . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file;

            if (file_exists($fn)) {
                return $fn;
            }
        }


        return $file;
    }

    public function __construct() {
        $this->init();
    }

    public function init() {

    }

    /**
     * Envio de email de acordo com as configurações globais do sistema
     *
     * @param string $subject Assunto
     * @param array $to Array (email => nome)
     * @param array $replyTo Array (email => nome)
     * @param string $htmlBody HTML
     * @param array $bcc array(array(email => nome))
     */
    public function quickMail($subject, array $to, array $replyTo, $htmlBody, array $bcc = array()) {

        $mailer = new \Nette\Mail\SmtpMailer(C::$mailer);

        $mail = new \Nette\Mail\Message;
        $mail->setFrom(C::$sender);
        $mail->addTo(key($to), current($to));
        $mail->setSubject($subject);
        foreach ($bcc as $k => $v) {
            $mail->addBcc($k, $v);
        }
        $mail->addReplyTo(key($replyTo), current($replyTo));
        $mail->setHtmlBody($htmlBody);

        $mailer->send($mail);
    }

    public function redirect($route, $messages = null) {

        if (is_array($route)) {
            $destination = A::get($route, 0);
            $params = A::get($route, 1, array());
            $get = A::get($route, 2, array());
        } else {
            $destination = $route;
            $params = array();
            $get = array();
        }
        $url = $this->link($destination, $params, $get);

        if (php_sapi_name() == 'cli') {
            return array('route' => $route, 'url' => $url, 'messages' => $messages);
        }

        if ($messages) {
            if (is_array($messages)) {
                foreach ($messages as $m) {
                    if (is_array($m)) {
                        $message = A::get($m, 0);
                        $level = A::get($m, 1, LOG_INFO);
                        flash($message, $level);
                    } else {
                        $message = reset($messages);
                        $level = end($messages);
                        flash($message, $level);
                        break;
                    }
                }
            } else {
                flash($messages);
            }
        }

        C::$response->redirect($url);
        die;
    }

}
