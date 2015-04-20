<?php

namespace Cdc\Definition\Helper;

use \Nette\Utils\Arrays as A;

class PasswordColumn extends Helper {

    public static function def($args = array()) {

        $confirmation = A::get($args, 'confirmation', false);

        $defaults = array(
            'type' => \Cdc\Definition::TYPE_COLUMN,
            \Cdc\Definition::TYPE_WIDGET => array(
                'widget' => 'password',
                'attributes' => array(
                    'maxlength' => 32,
                ),
            ),
            \Cdc\Definition::OPERATION => array(
                'create' => array(),
                'update' => array(),
                'change_password' => array(),
                DEFAULT_OPERATION => array(),
            ),
            \Cdc\Definition::TYPE_RULE => array(
                array('\Cdc\Rule\Length', array(0, 32)),
            ),
        );

        if ($confirmation) {
            $defaults[\Cdc\Definition::TYPE_RULE][] = array('\Cdc\Rule\CompareFields', array($confirmation, label($confirmation)));
            $defaults['tags'] = array('placeholder');
        }

        return self::merge($defaults, $args);
    }

}
