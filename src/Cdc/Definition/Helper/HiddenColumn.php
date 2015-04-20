<?php

namespace Cdc\Definition\Helper;

class HiddenColumn extends Helper {

    public static function def($args = array()) {
        $col = TextColumn::def($args);
        $col[\Cdc\Definition::TYPE_WIDGET]['widget'] = 'hidden';
        return $col;
    }

}
