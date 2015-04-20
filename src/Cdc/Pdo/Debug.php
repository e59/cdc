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
 * PDO que loga todas as consultas feitas.
 */
class Debug extends \PDO {

    /**
     *
     * @var logger
     */
    public $logger;

    public function __construct($dsn, $username = null, $passwd = null, $options = null) {
        $this->logger = new \Cdc\Pdo\Logger;
        $options[\PDO::ATTR_STATEMENT_CLASS] = array('\Cdc\Pdo\Statement');
        return parent::__construct($dsn, $username, $passwd, $options);
    }

    public function prepare($statement, $driver_options = array()) {
        $start = $this->logger->startLog(__FUNCTION__, $statement);
        $p = parent::prepare($statement, $driver_options);
        $this->logger->endLog($start, $statement);
        $p->logger = $this->logger;
        return $p;
    }

    public function exec($statement) {
        $start = $this->logger->startLog(__FUNCTION__, $statement);
        $result = parent::exec($statement);
        $this->logger->endLog($start, $statement);
        return $result;
    }

    public function query($statement) {
        $start = $this->logger->startLog(__FUNCTION__, $statement);
        $result = parent::query($statement);
        $this->logger->endLog($start, $statement);
        return $result;
    }

    public function beginTransaction() {
        $start = $this->logger->startLog(__FUNCTION__, 'begin transaction');
        $result = parent::beginTransaction();
        $this->logger->endLog($start, 'begin transaction');
        return $result;
    }

    public function commit() {
        $start = $this->logger->startLog(__FUNCTION__, 'commit');
        $result = parent::commit();
        $this->logger->endLog($start, 'commit');
        return $result;
    }

    public function rollBack() {
        $start = $this->logger->startLog(__FUNCTION__, 'rollback');
        $result = parent::rollBack();
        $this->logger->endLog($start, 'rollBack');
        return $result;
    }

}
