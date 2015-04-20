<?php

namespace Cdc\Definition\Helper;

use \Nette\Utils\Arrays as A;

class TextColumn extends Helper {

    public static function def($args = array()) {

        $minLength = A::get($args, 'min', 0);
        $maxLength = A::get($args, 'max', 150);

        $defaults = array(
            'type' => \Cdc\Definition::TYPE_COLUMN,
            \Cdc\Definition::TYPE_WIDGET => array(
                'attributes' => array(
                    'maxlength' => $maxLength,
                ),
            ),
            \Cdc\Definition::OPERATION => self::$defaultOperations,
            \Cdc\Definition::TYPE_RULE => array(
                'trim' => array('\Cdc\Rule\Trim'),
                'length' => array('\Cdc\Rule\Length', array($minLength, $maxLength)),
            ),
        );

        return self::merge($defaults, $args);
    }

}
