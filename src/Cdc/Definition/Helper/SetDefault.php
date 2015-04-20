<?php

namespace Cdc\Definition\Helper;

use \Nette\Utils\Arrays as A;

class SetDefault {

    public static function modify($args = array(), $value = true) {

        $args[\Cdc\Definition::TYPE_WIDGET]['attributes']['default'] = $value;

        return $args;
    }

}
