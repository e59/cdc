<?php

namespace Cdc\Definition\Helper;

class BooleanColumn extends Helper {

    public static function def($args = array()) {

        $defaults = array(
            'type' => \Cdc\Definition::TYPE_COLUMN,
            'tags' => ['boolean'],
            \Cdc\Definition::TYPE_WIDGET => array(
                'widget' => 'boolean',
            ),
            \Cdc\Definition::OPERATION => self::$defaultOperations,
            \Cdc\Definition::TYPE_RULE => array(
                array('\Cdc\Rule\Trim'),
                array('\Cdc\Rule\Boolean'),
            ),
        );


        return self::merge($defaults, $args);
    }

}
