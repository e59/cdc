<?php

namespace Cdc\Definition\Helper;

use \Nette\Utils\Arrays as A;

class SimpleSelectColumn extends Helper {

    public static function def($args = array()) {
        $defaults = array(
            'type' => \Cdc\Definition::TYPE_COLUMN,
            \Cdc\Definition::TYPE_WIDGET => array(
                'widget' => 'select',
            ),
            \Cdc\Definition::OPERATION => self::$defaultOperations,
            \Cdc\Definition::TYPE_RULE => array(
                array('\Cdc\Rule\Trim'),
                array('Cdc\Rule\ArrayKeyExists'),
            ),
        );

        $values = A::get($args, 'values');

        $maybeCallable = A::get($values, 0, false);



        if (is_callable($maybeCallable)) {
            $defaults[\Cdc\Definition::TYPE_WIDGET]['callback'] = $values;
        } else {
            $defaults[\Cdc\Definition::TYPE_WIDGET]['options'] = $values;
        }

        return self::merge($defaults, $args);
    }

}
