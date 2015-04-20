<?php

namespace Cdc\Pdo;

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
 * @subpackage Cdc_Pdo
 */

/**
 * Classe de comodidade para para forçar que o PDO usado na classe
 * que estende esta emitirá exceptions no caso de erro.
 */
class Container {

    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * Argumentos genéricos
     *
     * @var mixed
     */
    protected $args;

    public function __construct($pdo = null, $args = null) {
        if (!$pdo) {
            if (class_exists('\C') && method_exists('\C', 'connection')) {
                $pdo = \C::connection();
            }
        }
        $this->setPdo($pdo);
        if ($args === null) {
            $args = array();
        }
        $this->args = $args;
    }

    public function setPdo(\PDO $pdo = null) {
        if ($pdo && $pdo->getAttribute(\PDO::ATTR_ERRMODE) != \PDO::ERRMODE_EXCEPTION) {
            throw new \Cdc\Pdo\Exception\Errmode;
        }
        $this->pdo = $pdo;
        return $this;
    }

    public function getPdo() {
        if (!$this->pdo) {
            throw new \Cdc\Pdo\Exception\PDO;
        }
        return $this->pdo;
    }

    public function ensureTransaction() {
        if (!$this->pdo->inTransaction()) {
            throw new \Cdc\Pdo\Exception\NotInTransaction;
        }
    }

}
