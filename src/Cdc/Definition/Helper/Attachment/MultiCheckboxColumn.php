<?php

namespace Cdc\Definition\Helper\Attachment;

use \Nette\Utils\Arrays as A;

class MultiCheckboxColumn extends \Cdc\Definition\Helper\Helper {

    public static function def($args = array()) {

        // just checking, it's required anyways
        A::get($args, 'query');
        A::get($args, 'local');
        A::get($args, 'parent');
        A::get($args, 'junction');
        A::get($args, 'id');

        $defaults = array(
            'type' => \Cdc\Definition::TYPE_ATTACHMENT,
            \Cdc\Definition::TYPE_WIDGET => array(
                'widget' => 'checkboxes',
            ),
            \Cdc\Definition::OPERATION => self::$defaultOperations,
            \Cdc\Definition::TYPE_RULE => array(
                array('\Cdc\Rule\ArrayKeyExists'),
            ),
        );

        $values = A::get($args, 'values');

        $possibleCallback = A::get($values, 0, false);

        if (is_callable($possibleCallback)) {
            $defaults[\Cdc\Definition::TYPE_WIDGET]['callback'] = $values;
        } else {
            $defaults[\Cdc\Definition::TYPE_WIDGET]['options'] = $values;
        }


        return self::merge($defaults, $args);
    }

}
