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

class CompareFields extends Rule {

    protected $_field;
    protected $_label;

    public function __construct($field, $label) {
        $this->_field = $field;
        $this->_label = $label;
    }

    public function check($index, &$row, $definition = null, $rowset = array()) {

        if (!isset($row[$index])) {
            return array();
        }

        $field_1 = $row[$index];
        $field_2 = $row[$this->_field];

        if (!strcmp($field_1, $field_2)) {
            return array();
        }

        return array('Não coincide com o campo "' . $this->_label . '".');
    }

}
