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

class RequiredIfTrue extends Rule {

    protected $_callback;
    protected $_related_index;

    public function __construct($callback, $relatedIndex) {
        $this->_callback = $callback;
        $this->_related_index = $relatedIndex;
    }

    public function check($index, &$row, $definition = null, $rowset = array()) {
        if (array_key_exists($index, $row) && call_user_func_array($this->_callback, array($this->_related_index, $index, $row, $definition, $rowset))) {
            if (is_array($row[$index])) {
                if (!empty($row[$index])) {
                    return array();
                }
            } elseif (mb_strlen($row[$index])) {
                return array();
            }
            return array('Preenchimento obrigatório.');
        }
        return array();
    }

}
