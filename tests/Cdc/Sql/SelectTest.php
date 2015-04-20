<?php

class Cdc_Sql_SelectTest extends PHPUnit_Framework_TestCase
{

    protected $object;

    protected function setUp()
    {
        $this->object = new Cdc_Sql_Select;
        $pdo = new PDO('sqlite::memory:', null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $this->object->setPdo($pdo);
        $pdo->query('create table teste(teste_id integer primary key, teste_texto text, teste_opcao boolean)');
        $pdo->exec('insert into teste (teste_id, teste_texto) values (1, "abc")');
    }

    public function testToString()
    {
        $this->object->cols = array('a', 'b');
        $this->object->from = array('teste');
        $expected = 'select a, b from teste';
        $this->assertEquals($expected, $this->object->__toString());

        $this->object->join = array('x' => array('left' => array('a =' => 'b', 'c = d')));
        $expected .= ' left join x on ((a = ?) and (c = d))';
        $this->assertEquals($expected, $this->object->__toString());

        $expected .= ' order by a asc';
        $this->object->order = array('a' => 'asc');
        $this->assertEquals($expected, $this->object->__toString());

        $expected .= ' limit 10 offset 40';
        $this->object->limit = array('limit' => 10, 'page' => 5);
        $this->assertEquals($expected, $this->object->__toString());

        $this->object->order = array();
        $this->object->join = array();
        $this->object->limit = array('limit' => 30);
        $this->object->where = array('m =' => 'n');
        $expected = 'select a, b from teste where (m = ?) limit 30';

        $this->object->cols = array('a', 'b');
        $this->object->schema = 'correios';
        $this->object->from = array('teste');
        $expected = 'select a, b from correios.teste';
        $this->assertEquals($expected, $this->object->__toString());
    }

}
