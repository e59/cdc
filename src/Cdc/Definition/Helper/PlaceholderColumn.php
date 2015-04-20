<?php

namespace Cdc\Definition\Helper;

class PlaceholderColumn extends Helper {

    public static function def($args = array()) {

        $defaults = array(
            'type' => \Cdc\Definition::TYPE_COLUMN,
            'hide' => true,
            \Cdc\Definition::OPERATION => self::$defaultOperations,
        );

        return self::merge($defaults, $args);
    }

}
