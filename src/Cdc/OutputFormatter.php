<?php

namespace Cdc;

class OutputFormatter {

    public static function arrayValue($value, $attribs, $input, $def, $encode, $params) {
        if (!$value) {
            return $value;
        }
        return f($params[0], $value);
    }

    public static function decimal($value, $attribs, $input, $def, $encode, $params) {
        if (!$value) {
            return $value;
        }
        if (isset($params[0])) {
            $decimals = $params[0];
        } else {
            $decimals = 2;
        }

        if (isset($params[1])) {
            $dec_point = $params[1];
        } else {
            $dec_point = ',';
        }

        if (isset($params[2])) {
            $thousands_sep = $params[2];
        } else {
            $thousands_sep = '';
        }

        $value = str_replace(',', '.', $value);
        return number_format($value, $decimals, $dec_point, $thousands_sep);
    }

    public static function date($value, $attribs, $input, $def, $encode, $params) {
        if (!$value) {
            return $value;
        }
        if (isset($params[0])) {
            $dateFormat = $params[0];
        } else {
            $dateFormat = 'd/m/Y';
        }
        return date($dateFormat, strtotime(str_replace('/', '-', $value)));
    }

    public static function mask($value, $attribs, $input, $def, $encode, $params) {
        $mask = $params[0];
        return mask($mask, $value);
    }

    public static function phone($value, $attribs, $input, $def, $encode, $params) {
        return get_masked_phone($value);
    }

    public static function integer($value, $attribs, $input, $def, $encode, $params) {
        $strict = f($params, 0);
        if ($strict === 'strict') {
            if (!$value) {
                return $value;
            }
        }

        return intval($value);
    }

}
