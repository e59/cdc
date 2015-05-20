<?php

namespace Cdc\Widget;

class Formatter {

    public static $dateFormat;

    public static $dateTimeFormat;

    public static function boolean(&$row, $rowset, $index, $args = array()) {
        if ($row[$index]) {
            $f = '<span class="fa fa-check"></span>';
        } else {
            $f = '';
        }
        $row[$index] = $f;
    }

    public static function date(&$row, $rowset, $index, $args = array()) {
        if (!self::$dateFormat) {
            self::$dateFormat = \C::$dateFormat;
        }
        if (!$row[$index]) {
            return null;
        }
        $row[$index] = date(self::$dateFormat, strtotime($row[$index]));
    }

    public static function datetime(&$row, $rowset, $index, $args = array()) {
        if (!self::$dateTimeFormat) {
            self::$dateTimeFormat = \C::$dateTimeFormat;
        }

        if (!$row[$index]) {
            return null;
        }

        $row[$index] = date(self::$dateTimeFormat, strtotime($row[$index]));
    }

}
