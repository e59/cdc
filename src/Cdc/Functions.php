<?php

/**
 * Cdc Toolkit
 *
 * Copyright 2012 Eduardo Marinho
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * 	 http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Eduardo Marinho
 * @package Cdc
 */
// @codeCoverageIgnoreStart

define('DEFAULT_OPERATION', 'default');
define('LOG_SUCCESS', LOG_DEBUG + 1);

function coalesce(/* ... */) {
    $args = func_get_args();
    foreach ($args as $arg) {
        if (null !== $arg && '' !== $arg) {
            return $arg;
        }
    }
}

function mask($mask, $string, $char = '#') {
    $string = str_replace(" ", "", $string);
    for ($i = 0; $i < strlen($string); $i++) {
        $pos = strpos($mask, $char);
        if ($pos !== false) {
            $mask[$pos] = $string[$i];
        }
    }
    return str_replace($char, '', $mask);
}

function label($index, $labels = array()) {
    if (!$labels) {
        $labels = C::$labels;
    }

    if (isset($labels[$index])) {
        if (is_string($labels[$index])) {
            return ucfirst($labels[$index]);
        }
    }
    return $index;
}

/**
 * DEPRECATED, use flash and don't redirect
 *
 * @param type $msg
 * @param type $level
 * @deprecated
 */
function event($msg, $level = LOG_INFO) {
    flash($msg, $level);
}

function flash($msg, $level = LOG_INFO) {
    if (!C::$session['flashes']) {
        C::$session['flashes'] = array();
    }

    $flashes = C::$session['flashes'];

    $message = array();
    $message['level'] = $level;
    $message['message'] = $msg;
    $flashes[] = $message;
    C::$session['flashes'] = $flashes;
}

function logLevel2Class($level) {
    $class = '';
    $button = '<button class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>';
    $icon = '<i class="fa fa-exclamation-sign"></i>';
    switch ($level) {
        case LOG_EMERG:
            $class = 'alert alert-block alert-danger level-emerg class-error';
            $icon = '<i class="fa fa-ban"></i>';
            break;
        case LOG_ALERT:
            $class = 'alert alert-block alert-danger level-alert class-error';
            $icon = '<i class="fa fa-ban"></i>';
            break;
        case LOG_CRIT:
            $class = 'alert alert-block alert-danger level-crit class-error';
            $icon = '<i class="fa fa-ban"></i>';
            break;
        case LOG_ERR:
            $class = 'alert alert-block alert-danger level-err class-error';
            $icon = '<i class="fa fa-ban"></i>';
            break;
        case LOG_WARNING:
            $class = 'alert alert-block alert-warning level-warning class-warning';
            $icon = '<i class="fa fa-info-circle"></i>';
            break;
        case LOG_INFO:
            $class = 'alert alert-block alert-info level-info class-warning';
            $icon = '<i class="fa fa-info-circle"></i>';
            break;
        case LOG_DEBUG:
            $class = 'alert alert-block alert-info level-debug class-warning';
            $icon = '<i class="fa fa-eye"></i>';
            break;
        default:
            $class = 'alert alert-block alert-success level-success class-success';
            $icon = '<i class="fa fa-check"></i>';
        //            break;
    }
    return compact('class', 'icon', 'button');
}

function display_system_events($return = false, $root = array()) {
    if (!C::$session['flashes']) {
        C::$session['flashes'] = array();
    }
    $root or $root = C::$session['flashes'];

    if (empty($root)) {
        return;
    }
    $events = '';
    foreach ($root as $ev) {
        $level = logLevel2Class($ev['level']);
        $events .= '<li class="' . $level['class'] . '">' . $level['icon'] . ' ' . $ev['message'] . ' ' . $level['button'] . '</li>';
    }

    C::$session['flashes'] = array();

    if ($events) {
        $events = '<ul class="system-messages">' . $events . '</ul>';
    }
    if ($return) {
        return $events;
    }

    echo $events;
}

function display_system_events_joined($return = false, $root = array()) {

    if (isset($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $v) {
            event($v['message'], $v['level']);
        }
        unset($_SESSION['flash']);
    }

    if (!isset($GLOBALS['events'])) {
        $GLOBALS['events'] = array();
    }

    $root or $root = $GLOBALS['events'];
    if (empty($root)) {
        return;
    }
    $accumulator = [];
    foreach ($root as $ev) {
        if (!isset($accumulator[$ev['level']])) {
            $accumulator[$ev['level']] = [];
        }
        $accumulator[$ev['level']][] = ['message' => $ev['message'], 'level' => $ev['level']];
    }

    $events = '';
    foreach ($accumulator as $key => $item) {
        $ev = [];
        foreach ($item as $msg) {
            $ev[$msg['message']] = $msg['message'];
        }
        if ($ev) {
            $level = logLevel2Class($key);
            $events .= '<li class="' . $level['class'] . '">' . $level['icon'] . ' ' . $level['button'] . ' ' . implode('<br>', $ev) . '</li>';
        }
    }

    if ($events) {
        $events = '<ul class="system-messages">' . $events . '</ul>';
    }

    if ($return) {
        return $events;
    }

    echo $events;
}

/**
 * Conditional SPRINTF.
 *
 * @param string $string
 * @param mixed $value Array (uses vsprintf), boolean (returns fallback if false) or else (uses sprintf)
 * @param fallback Fallback value
 */
function csprintf($string, $value, $fallback = null) {
    if (true === $value) {
        return $string;
    }
    if (false === $value) {
        return $fallback;
    }

    if ($value) {
        if (is_array($value)) {
            return vsprintf($string, $value);
        } else {
            return sprintf($string, $value);
        }
    } else {
        if ($fallback) {
            return sprintf($string, $fallback);
        }
    }
}

/**
 * Fuck illuminate
 */
function e($var) {
    echo r($var);
}

function r($var) {
    return htmlentities(t($var), ENT_QUOTES, 'UTF-8');
}

/**
 * Future _()
 */
function t($text) {
    return $text;
}

function f($type, $variable_name, $filter = FILTER_DEFAULT, $options = null) {
    if (is_array($type)) {
        if (isset($type[$variable_name])) {

            if (is_array($type[$variable_name])) {
                return filter_var_array($type[$variable_name], $filter, $options);
            }

            return filter_var($type[$variable_name], $filter, $options);
        }
        return null;
    }
    return filter_input($type, $variable_name, $filter, $options);
}

function turn_off_magic_quotes() {
    // http://php.net/manual/pt_BR/security.magicquotes.disabling.php
    if (get_magic_quotes_gpc()) {
        $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
        while (list($key, $val) = each($process)) {
            foreach ($val as $k => $v) {
                unset($process[$key][$k]);
                if (is_array($v)) {
                    $process[$key][stripslashes($k)] = $v;
                    $process[] = &$process[$key][stripslashes($k)];
                } else {
                    $process[$key][stripslashes($k)] = stripslashes($v);
                }
            }
        }
        unset($process);
    }
}

// @codeCoverageIgnoreEnd
