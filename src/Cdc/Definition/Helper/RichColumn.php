<?php

namespace Cdc\Definition\Helper;

class RichColumn extends Helper {

    public static function def($args = array()) {
        $defaults = array(
            'type' => \Cdc\Definition::TYPE_COLUMN,
            \Cdc\Definition::TYPE_WIDGET => array(
                'widget' => 'rich',
            ),
            \Cdc\Definition::OPERATION => self::$defaultOperations,
            \Cdc\Definition::TYPE_RULE => array(
                array('\Cdc\Rule\Trim'),
            ),
        );

        return self::merge($defaults, $args);
    }

}
