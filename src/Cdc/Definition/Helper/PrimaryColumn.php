<?php

namespace Cdc\Definition\Helper;

class PrimaryColumn extends Helper {

    public static function def($args = array()) {
        $defaults = array(
            'type' => \Cdc\Definition::TYPE_COLUMN,
            'tags' => array('primary', 'order desc'),
            'primary' => true,
            'hide' => true,
            \Cdc\Definition::OPERATION => self::$defaultOperations,
        );

        return self::merge($defaults, $args);
    }

}
