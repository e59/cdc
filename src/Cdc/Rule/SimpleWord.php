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

class SimpleWord extends Rule {

    /**
     * Remover também _ e números do primeiro caractere
     * @var type
     */
    protected $evenSimpler;

    public function __construct($evenSimpler = false) {
        $this->evenSimpler = $evenSimpler;
    }

    public function check($index, &$row, $definition = null, $rowset = array()) {
        if (!isset($row[$index])) {
            return array();
        }
        $mensagens = array();
        $sanitized = preg_replace('#\W#', '', strtolower(filter_var($row[$index], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH)));

        $lascouTudo = false;
        if ($this->evenSimpler) {
            $old = $sanitized;
            $sanitized = preg_replace('#^[_|\d]#', '', $sanitized);
            if ($old != $sanitized) {
                $lascouTudo = true;
            }
        }
        if ($sanitized != $row[$index]) {
            $mensagens[] = 'Valor inválido. Utilize apenas letras minúsculas, números ou _ para este campo.';
            if ($lascouTudo) {
                $mensagens[] = 'Não é permitido números ou _ como caractere inicial deste campo.';
            }
        }
        return $mensagens;
    }

}
