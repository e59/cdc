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

class ArrayKeyExists extends Rule {

    private $dataset = null;

    public function __construct($dataset = null) {
        if (null !== $dataset) {
            $this->dataset = $dataset;
        }
    }

    public function check($index, &$row, $definition = null, $rowset = array()) {

        $mensagens = array();
        if (!isset($row[$index])) {
            return array();
        }

        if (!$row[$index] && (string) $row[$index] !== 0) {
            return array();
        }

        // @TODO: Update this when fixing form class

        if (null !== $this->dataset) {
            $options = $this->dataset;
        } else {
            $options = \Cdc\Form::obterOpcoes($definition[$index][\Cdc\Definition::TYPE_WIDGET]);
        }



        $data = \Cdc\Form::obterValor(array('name' => $index), $row, f($definition, $index));

        if (is_array($data)) {
            foreach ($data as $key => $v) {
                if (!array_key_exists($key, $options)) {
                    $mensagens[] = 'Opção inválida: ' . $key;
                }
            }
        } else {
            if (!array_key_exists($data, $options)) {
                $mensagens[] = 'Opção inválida.';
            }
        }


        return $mensagens;
    }

}
