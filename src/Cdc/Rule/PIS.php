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
 * @subpackage Cdc_Rule
 */

namespace Cdc\Rule;

class PIS extends Rule {

    public function check($index, &$row, $definition = null, $rowset = array()) {
        if (!isset($row[$index]) || !$row[$index]) {
            return array();
        }

        $row[$index] = str_pad(preg_replace('#\D#', '', $row[$index]), 11, '0', STR_PAD_LEFT);

        if (self::validate($row[$index])) {
            return array();
        }

        return array('PIS inválido.');
    }

    public static function validate($pis) {
        $ftap = '3298765432';
        $total = 0;
        $resto = 0;

        for ($i = 0; $i <= 9; $i++) {
            $resultado = substr($pis, $i, 1) * substr($ftap, $i, 1);
            $total += $resultado;
        }

        $resto = $total % 11;

        if ($resto) {
            $resto = 11 - $resto;
        }

        if ($resto == 10 || $resto == 11) {
            $resto = substr($resto, 1, 1);
        }

        return $resto == substr($pis, 10, 1);
    }

}
