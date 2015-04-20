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

class AtLeastOne extends Rule {

    protected $_fields;
    protected $_message = 'Preenchimento obrigatório de pelo menos um dos campos relacionados.';

    public function __construct($fields, $message = null) {
        $this->_fields = $fields;
        if ($message) {
            $this->_message = $message;
        }
    }

    public function check($index, &$row, $definition = null, $rowset = array()) {
        $fields = $this->_fields;
        $fields[] = $index;
        foreach ($fields as $f) {
            if (array_key_exists($f, $row)) {
                if (is_array($row[$f])) {
                    if (!empty($row[$f])) {
                        return array();
                    }
                } elseif (mb_strlen($row[$f])) {
                    return array();
                }
            }
        }
        return array($this->_message);
    }

}
