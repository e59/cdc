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
 * Pool de conexões PDO.
 *
 * As conexões são registradas mas ela só é efetivamente feita quando há uma
 * chamada a getConnection().
 *
 */
class Pool {

    protected static $registered_connections = array();
    protected static $connections;

    const NORMAL = 1;
    const DEBUG = 2;

    public static function register($index = 'default', $dsn = null, $username = null, $passwd = null, $options = null) {
        $options or $options = array(
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        unset(self::$connections[$index]);
        self::$registered_connections[$index] = array(
            'dsn' => $dsn,
            'username' => $username,
            'passwd' => $passwd,
            'options' => $options,
        );
    }

    /**
     *
     * @param string $index
     * @param string $mode
     * @return PDO
     */
    public static function getConnection($index = 'default', $mode = self::NORMAL, $init_queries = array()) {
        if (!isset(self::$connections[$mode][$index])) {
            if ($mode == self::NORMAL) {
                $class_name = '\PDO';
            } else {
                $class_name = '\Cdc\Pdo\Debug';
                $mode = self::DEBUG;
            }
            $rc = new \ReflectionClass($class_name);
            $pdo = $rc->newInstanceArgs(self::$registered_connections[$index]);
            self::$connections[$mode][$index] = $pdo;
            foreach ($init_queries as $q) {
                $pdo->query($q);
            }
        }

        return self::$connections[$mode][$index];
    }

}
