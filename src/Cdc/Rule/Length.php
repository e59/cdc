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

class Length extends Rule {

    protected $_min = 0;
    protected $_max = 0;
    protected $_required = false;

    public function __construct($min, $max, $required = false) {
        if ($min != -1) {
            $this->_min = $min;
        }
        if ($max != -1) {
            $this->_max = $max;
        }
        $this->_required = $required;
    }

    public function check($index, &$row, $definition = null, $rowset = array()) {
        $mensagens = array();

        if (array_key_exists($index, $row)) {
            $length = mb_strlen($row[$index]);
        } else {
            $length = 0;
        }

        $invalid_max = false;
        $invalid_min = false;

        if ($this->_max && ($length > $this->_max)) {
            $invalid_max = true;
        }

        if ($length < $this->_min) {
            $invalid_min = true;
        }


        if (!$length && !$this->_required) {
            $invalid_min = false;
        }

        if ($invalid_min) {
            return array('Deve ter no mínimo ' . $this->_min . ' caractere(s).');
        }

        if ($invalid_max) {
            return array('Deve ter no máximo ' . $this->_max . ' caractere(s).');
        }

        return array();
    }

}
