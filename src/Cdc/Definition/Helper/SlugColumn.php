<?php

namespace Cdc\Definition\Helper;

class SlugColumn extends DefinitionHelper {

    public static function def($args = array()) {

        $defaults = array(
            'type' => \Cdc\Definition::TYPE_COLUMN,
            \Cdc\Definition::OPERATION => array(
                'item' => array(),
                DEFAULT_OPERATION => array(),
            ),
        );

        return self::merge($defaults, $args);
    }

}
