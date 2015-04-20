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
 */

namespace Cdc;

use \Cdc\Definition as D;

/**
 * Processador de regras de filtro e validação.
 *
 */
class Rule {

    protected $queryResult;
    protected $data;
    protected $clean_data;
    protected $messages = array();

    public function __construct(array $queryResult = array()) {
        $this->setQueryResult($queryResult);
    }

    public function getQueryResult() {
        return $this->queryResult;
    }

    public function setQueryResult($queryResult) {
        $this->queryResult = $queryResult;
        return $this;
    }

    public function getMessages() {
        return $this->messages;
    }

    public function invoke(&$row = null, &$rowset = array()) {
        $queryResult = $this->getQueryResult();

        if (empty($queryResult)) {
            return true;
        }

        $messages = array();
        foreach ($queryResult as $key => $value) {
            if (!array_key_exists(D::TYPE_RULE, $value)) {
                continue;
            }

            foreach ($value[D::TYPE_RULE] as $c) {
                if (is_callable($c)) {
                    $class_name = get_class($c[0]);
                    if (!array_key_exists($class_name, $this->rules)) {
                        $class = $c[0];
                    }
                } else {
                    $class_name = reset($c);
                    if (count($c) == 1) {
                        $class = new $class_name;
                    } else {
                        $rc = new \ReflectionClass($class_name);
                        $class = $rc->newInstanceArgs($c[1]);
                    }

                    $c = array($class, 'check');
                }

                if (!is_callable($c)) {
                    throw new \Cdc\Rule\Exception\InvalidCallback;
                }

                $result = call_user_func_array($c, array($key, &$row, $queryResult, $rowset));

                if (!empty($result)) {
                    if (!isset($messages[$key])) {
                        $messages[$key] = array();
                    }
                    $messages[$key] = array_merge($result, (array) $messages[$key]);
                }
            }
        }

        $this->messages = $messages;

        if (empty($messages)) {
            return true;
        }

        return false;
    }

}
