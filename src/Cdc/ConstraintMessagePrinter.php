<?php

namespace Cdc;

class ConstraintMessagePrinter {

    public static function flatArray($messages, $labels = array(), $label_function = 'label') {
        if (!$labels) {
            $labels = \C::$labels;
        }
        $flattened = array();
        foreach ($messages as $key => $m) {
            foreach ($m as $message) {
                $flattened[] = $label_function($key, $labels) . ': ' . $message;
            }
        }
        return $flattened;
    }

    public static function event($messages, $labels = array(), $label_function = 'label') {
        if (!$labels) {
            $labels = \C::$labels;
        }
        foreach ($messages as $key => $m) {
            foreach ($m as $message) {
                event('<strong class="field-error" data-field="' . $key . '">' . $label_function($key, $labels) . ': </strong>' . $message, LOG_ERR);
            }
        }
    }

}
