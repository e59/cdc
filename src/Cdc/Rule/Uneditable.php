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

class Uneditable extends Rule {

    public function check($index, &$row, $definition = null, $rowset = array()) {



        foreach ($definition[$index][Cdc_Definition::TYPE_RULE] as $rule) {
            $name = reset($rule);
            if ($name == '\Cdc\Rule\Required') {
                $row[$index] = new \Cdc\Rule\Signal\Uneditable;
                return array();
            }
        }

        unset($row[$index]);
        return array();
    }

}
