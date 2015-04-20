<?php

namespace Cdc\Definition\Helper\Attachment;

use \Nette\Utils\Arrays as A;
use \Cdc\Definition\Helper\Helper;

class PlaceholderColumn extends Helper {

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
                'widget' => 'none',
            ),
            \Cdc\Definition::OPERATION => self::$defaultOperations,
            \Cdc\Definition::TYPE_RULE => array(
            ),
        );

        return self::merge($defaults, $args);
    }

}
