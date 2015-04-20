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

class CompareDate extends Rule {

    protected $_field;
    protected $_label;
    protected $_format = 'd/m/Y';

    public function __construct($field, $label, $format = null) {
        $this->_field = $field;
        $this->_label = $label;
        if ($format) {
            $this->_format = $format;
        }
    }

    public function check($index, &$row, $definition = null, $rowset = array()) {
        $field_1 = DateTime::createFromFormat($this->_format, $row[$index]);
        $field_2 = DateTime::createFromFormat($this->_format, $row[$this->_field]);
        $dt1 = strtotime($field_1->format('Y-m-d H:i:s'));
        $dt2 = strtotime($field_2->format('Y-m-d H:i:s'));

        if ($dt2 > $dt1) {
            return array();
        }

        return array('Não deve ser após o campo "' . $this->_label . '".');
    }

}
