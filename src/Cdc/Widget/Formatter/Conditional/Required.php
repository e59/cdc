<?php

namespace Cdc\Widget\Formatter\Conditional;

class Required {

    public static function invoke($def, $requiredText = '<span class="required_token"> *</span>') {
        if (isset($def[\Cdc\Definition::TYPE_WIDGET]['attributes']['required'])) {
            return $requiredText;
        }

        if (array_key_exists(\Cdc\Definition::TYPE_RULE, $def)) {
            $index = array_search(array('\Cdc\Rule\Required'), $def[\Cdc\Definition::TYPE_RULE]);
            if ($index !== false) {
                return $requiredText;
            }
        }
    }

}
