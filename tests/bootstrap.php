<?php

$base_dir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;

include $base_dir . 'src' . DIRECTORY_SEPARATOR . 'Cdc' . DIRECTORY_SEPARATOR . 'Core.php';

spl_autoload_register(array('Cdc_Core', 'autoload'));

date_default_timezone_set($GLOBALS['TIMEZONE']);

class DBTestCase extends PHPUnit_Extensions_Database_TestCase
{

    /**
     *
     * @var PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected $_connection;

    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        if (!$this->_connection)
        {
            $pdo = new Cdc_Pdo_Debug($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'], array(
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    ));

            $pdo->exec('create table test(id integer primary key autoincrement, date timestamp, email varchar(35) not null unique, body text, option boolean)');
            $pdo->exec('create table category(id integer primary key autoincrement, name varchar(255))');
            $pdo->exec('create table subcategory(id integer primary key autoincrement, category_id integer, name varchar(255), foreign key (category_id) references category(id) on delete cascade on update cascade)');
            $pdo->exec('create table test_category(test_id integer not null, category_id integer not null, foreign key (test_id) references test(id) on delete cascade on update cascade, foreign key (category_id) references category(id) on delete cascade on update cascade)');
            $pdo->exec('
CREATE TABLE usuario
(
    id integer primary key autoincrement,
    nome varchar(255) not null,
    email varchar(255) not null unique,
    senha varchar(60) not null,
    token varchar(72),
    token_validade timestamp,
    administrador boolean not null default false
)');



            $this->_connection = $this->createDefaultDBConnection($pdo, $GLOBALS['DB_DBNAME']);
        }
        return $this->_connection;
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        // return $this->createXMLDataSet(__DIR__ . '/database-seed.xml');
        return $this->createXMLDataSet(__DIR__ . '/database-seed.xml');
    }

}
