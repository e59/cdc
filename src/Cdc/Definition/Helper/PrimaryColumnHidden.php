<?php

namespace Cdc\Definition\Helper;

class PrimaryColumnHidden extends Helper {

    public static function def($args = array()) {

        $defaults = array(
            'type' => \Cdc\Definition::TYPE_COLUMN,
            'primary' => true,
            \Cdc\Definition::OPERATION => self::$defaultOperations,
            \Cdc\Definition::TYPE_WIDGET => array(
                'widget' => 'hidden',
            ),
            \Cdc\Definition::TYPE_RULE => array(
                array('\Cdc\Rule\Trim'),
            ),
        );

        return self::merge($defaults, $args);
    }

}
