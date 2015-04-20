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

class Age extends Rule {

    protected $_format = 'Y-m-d H:i:s';
    protected $_age;
    protected $_min_age;

    public function __construct($age, $format = null, $min_age = null) {
        $this->_age = $age;
        $this->_min_age = $min_age;

        if ($format) {
            $this->_format = $format;
        }
    }

    public function check($index, &$row, $definition = null, $rowset = array()) {

        if (!$row[$index]) {
            $row[$index] = null;
            return array();
        }

        $currentDate = DateTime::createFromFormat($this->_format, date($this->_format));
        $initialDate = DateTime::createFromFormat($this->_format, $row[$index]);
        $result = $currentDate->diff($initialDate);

        if ($this->_min_age) {
            if ($result->y < $this->_min_age || $result->y > $this->_age) {
                return array(sprintf('Idade fora da faixa permitida - %d até %d.', $this->_min_age, $this->_age));
            }
        } elseif ($result->y < $this->_age) {
            return array(sprintf('Mínimo de %d anos (%d informado).', $this->_age, $result->y));
        }

        return array();
    }

}
