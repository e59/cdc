<?php

namespace Cdc\Definition\Helper\Attachment;

use \Nette\Utils\Arrays as A;
use \Cdc\Definition\Helper\Helper;

class MultiFileColumn extends Helper {

    public static function def($args = array()) {

        $defaults = array(
            \Cdc\Definition::TYPE_WIDGET => array(
                'widget' => 'multifile',
            ),
        );

        return FileColumn::def(self::merge($defaults, $args));
    }

}
