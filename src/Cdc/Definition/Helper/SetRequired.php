<?php

namespace Cdc\Definition\Helper;

use \Nette\Utils\Arrays as A;

class SetRequired {

    public static function modify($args = array()) {

        $args[\Cdc\Definition::TYPE_WIDGET]['attributes']['required'] = 'required';

        $rules = A::get($args, \Cdc\Definition::TYPE_RULE, array());

        $required = array('\Cdc\Rule\Required');

        /* É necessário inserir isso depois de trim */

        $trimIndex = false;
        foreach ($rules as $index => $rule) {
            $ruleClass = reset($rule);
            if ($ruleClass == '\Cdc\Rule\Trim') {
                $trimIndex = $index;
                break;
            }
        }

        if (false !== $trimIndex) {
            A::insertAfter($rules, $trimIndex, array(/* if this key is removed, overwriting will occur */ 'required' => $required));
        } else {
            array_unshift($rules, $required);
        }

        $args[\Cdc\Definition::TYPE_RULE] = $rules;

        return $args;
    }

}
