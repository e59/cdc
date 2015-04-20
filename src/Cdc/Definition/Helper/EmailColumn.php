<?php

namespace Cdc\Definition\Helper;

class EmailColumn extends Helper {

    public static function def($args = array()) {

        $defaults = array(
            'type' => \Cdc\Definition::TYPE_COLUMN,
            \Cdc\Definition::TYPE_WIDGET => array(
                'widget' => 'email',
                'attributes' => array(
                    'maxlength' => 255,
                ),
            ),
            \Cdc\Definition::OPERATION => self::$defaultOperations,
            \Cdc\Definition::TYPE_RULE => array(
                array('\Cdc\Rule\Trim'),
                array('\Cdc\Rule\Email'),
            ),
        );

        return self::merge($defaults, $args);
    }

}
