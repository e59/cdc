<?php

namespace Cdc\Definition\Helper;

use \Nette\Utils\Arrays as A;

class DateColumn extends Helper {

    public static function def($args = array()) {
        $format = A::get($args, 'format', 'd/m/Y');

        $defaults = array(
            'type' => \Cdc\Definition::TYPE_COLUMN,
            \Cdc\Definition::TYPE_WIDGET => array(
                'widget' => 'date',
                'output_callback' => array(array('\Cdc\OutputFormatter', 'date'), array($format)),
            ),
            \Cdc\Definition::OPERATION => array(
                'read' => array(
                    \Cdc\Definition::FORMATTER => array(
                        array('Cdc_CellDataFormatter', 'formatDate'), array($format),
                    ),
                ),
                'item' => array(),
                'create' => array(),
                'update' => array(),
                'delete' => array(),
                DEFAULT_OPERATION => array(),
            ),
            \Cdc\Definition::TYPE_RULE => array(
                array('\Cdc\Rule\Trim'),
                array('\Cdc\Rule\Date', array($format)),
            ),
        );

        return self::merge($defaults, $args);
    }

}
