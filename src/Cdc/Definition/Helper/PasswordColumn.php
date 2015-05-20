<?php

namespace Cdc\Definition\Helper;

use \Nette\Utils\Arrays as A;

class PasswordColumn extends Helper {

    public static function def($args = array()) {

        $confirmation = A::get($args, 'confirmation', false);
        $minLength = A::get($args, 'min', 0);
        $maxLength = A::get($args, 'max', 32);

        $defaults = array(
            'type' => \Cdc\Definition::TYPE_COLUMN,
            \Cdc\Definition::TYPE_WIDGET => array(
                'widget' => 'password',
                'attributes' => array(
                    'maxlength' => $maxLength,
                ),
            ),
            \Cdc\Definition::OPERATION => array(
                'create' => array(),
                'update' => array(),
                'change_password' => array(),
                DEFAULT_OPERATION => array(),
            ),
            \Cdc\Definition::TYPE_RULE => array(
                'length' => array('\Cdc\Rule\Length', array($minLength, $maxLength)),
            ),
        );

        if ($confirmation) {
            $defaults[\Cdc\Definition::TYPE_RULE][] = array('\Cdc\Rule\CompareFields', array($confirmation, label($confirmation)));
            $defaults['tags'] = array('placeholder');
        }

        return self::merge($defaults, $args);
    }

}
