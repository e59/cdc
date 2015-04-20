<?php

namespace Cdc\Definition\Helper\Attachment;

use \Nette\Utils\Arrays as A;
use \Cdc\Definition\Helper\Helper;

class FileColumn extends Helper {

    public static function def($args = array()) {

        // just checking, it's required anyways
        A::get($args, 'query');
        A::get($args, 'local');
        A::get($args, 'parent');
        A::get($args, 'junction');
        A::get($args, 'id');

        $extensions = A::get($args, 'extensions');
        $files = A::get($args, 'files');

        $defaults = array(
            'type' => \Cdc\Definition::TYPE_ATTACHMENT,
            'tags' => array('upload'),
            \Cdc\Definition::TYPE_WIDGET => array(
                'widget' => 'file',
                'attributes' => array(),
            ),
            \Cdc\Definition::OPERATION => self::$defaultOperations,
            \Cdc\Definition::TYPE_RULE => array(
                array('\Cdc\Rule\FileType', array($files, $extensions)),
            ),
        );

        return self::merge($defaults, $args);
    }

}
