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
 * @CodeCoverageIgnore
 */
class Statement extends \PDOStatement {

    public $logger;
    private $params = array();

    public function bindValue($parameter, $value, $data_type = \PDO::PARAM_STR) {
        $this->params[$parameter] = $value;
        return parent::bindValue($parameter, $value, $data_type);
    }

    public function execute($input_parameters = null) {
        $start = $this->logger->startLog(__FUNCTION__, print_r($this->params, true));
        $result = parent::execute($input_parameters);
        $this->logger->endLog($start, $this->queryString);
        return $result;
    }

}
