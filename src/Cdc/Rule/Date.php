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

class Date extends Rule {

    protected $_format = 'Y-m-d H:i:s';

    public function __construct($format = null) {
        if ($format) {
            $this->_format = $format;
        }
    }

    public function check($index, &$row, $definition = null, $rowset = array()) {

        if (!f($row, $index)) {
            $row[$index] = null;
            return array();
        }

        /*
          $dt = DateTime::createFromFormat($this->_format, $row[$index]);
          $errors = DateTime::getLastErrors();
          if (false === $dt || ($errors['warning_count'] || $errors['error_count']))
          {
          return array('Data inválida.');
          }
         *
         */

        $data_informada = $row[$index];
        $data_informada = preg_replace('#(\d{2})/(\d{2})/(\d{4})#', '$3-$2-$1', $data_informada);
        $valid_timestamp = strtotime($data_informada);
        if ($valid_timestamp === false || $valid_timestamp === -1) {
            return array('Data inválida.');
        }

        return array();
    }

}
