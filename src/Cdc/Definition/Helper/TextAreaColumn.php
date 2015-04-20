<?php

namespace Cdc\Definition\Helper;

use \Nette\Utils\Arrays as A;

class TextAreaColumn extends Helper {

    public static function def($args = array()) {

        $cols = $defaults = array(
            'type' => \Cdc\Definition::TYPE_COLUMN,
            \Cdc\Definition::TYPE_WIDGET => array(
                'widget' => 'textarea',
            ),
            \Cdc\Definition::OPERATION => self::$defaultOperations,
            \Cdc\Definition::TYPE_RULE => array(
                array('\Cdc\Rule\Trim'),
            ),
        );

        return self::merge($defaults, $args);
    }

}
