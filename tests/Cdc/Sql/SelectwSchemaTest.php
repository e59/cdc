<?php

class Cdc_Sql_SelectwSchemaTest extends PHPUnit_Framework_TestCase
{

    protected $object;

    protected function setUp()
    {
        $this->object = new Cdc_Sql_Select;
        $pdo = new PDO('pgsql:host=localhost;dbname=step', 'postgres', 'pgadmin', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $this->object->setPdo($pdo);
        $pdo->exec('set search_path to test;');
        $pdo->query('create table teste(teste_id integer primary key, teste_texto text, teste_opcao boolean)');
        $pdo->exec('insert into teste (teste_id, teste_texto) values (1, "abc")');
    }

    public function testToString()
    {
        $this->object->cols = array('a', 'b');
        $this->object->schema = 'correios';
        $this->object->from = array('teste');
        $expected = 'select a, b from correios.teste';
        $this->assertEquals($expected, $this->object->__toString());
    }

}
