<?php

namespace Cdc\Definition\Helper;

use \Nette\Utils\Arrays as A;

abstract class Helper implements HelperInterface {

    public static $defaultOperations = array(
        'read' => array(),
        'item' => array(),
        'create' => array(),
        'update' => array(),
        'delete' => array(),
        DEFAULT_OPERATION => array(),
    );

    /**
     * Removes deleted keys (by setting them to == null) and merges settings
     *
     * @param array $defaults Default settings
     * @param array $custom Custom settings
     * @return array
     */
    public static function merge($defaults, $custom) {

        return A::mergeTree($custom, $defaults);
    }

}
