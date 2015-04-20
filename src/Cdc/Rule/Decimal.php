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

class Decimal extends Rule {

    protected $_decimals = 2;
    protected $_dec_point = ',';
    protected $_thousands_sep = '.';

    public function __construct($decimals = 2, $decimalComma = true) {
        $this->_decimals = $decimals;

        if (!$decimalComma) {
            $this->_dec_point = '.';
            $this->_thousands_sep = ',';
        }
    }

    public function check($index, &$row, $definition = null, $rowset = array()) {
        if (array_key_exists($index, $row)) {
            // 324.323,34

            $row[$index] = coalesce(preg_replace('#[^\d.]#', '', preg_replace('#,#', '.', preg_replace('#\.#', '', $row[$index]))), 0);


            if (filter_var($row[$index], FILTER_VALIDATE_FLOAT | FILTER_VALIDATE_INT) !== false) {
                return array();
            }

            return array('Número inválido.');
        }
        return array();
    }

}
