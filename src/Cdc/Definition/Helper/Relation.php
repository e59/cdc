<?php

namespace Cdc\Definition\Helper;

class Relation extends Helper {

    public static function def($args = array()) {
        $defaults = array(
            'type' => \Cdc\Definition::TYPE_RELATION,
            'statement_type' => \Cdc\Definition::STATEMENT_SELECT,
            \Cdc\Definition::OPERATION => array(
                'read' => array(),
                'item' => array(),
                DEFAULT_OPERATION => array(),
                'create' => array(
                    'statement_type' => \Cdc\Definition::STATEMENT_INSERT,
                ),
                'update' => array(
                    'statement_type' => \Cdc\Definition::STATEMENT_UPDATE,
                ),
                'delete' => array(
                    'statement_type' => \Cdc\Definition::STATEMENT_DELETE,
                ),
            ),
        );

        return self::merge($defaults, $args);
    }

}
