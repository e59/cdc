<?php

class Cdc_Sql_InsertTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Insert
     */
    protected $object;
    protected $pdo;

    protected function setUp()
    {
        $this->object = new Cdc_Sql_Insert;
        $this->pdo = new PDO('sqlite::memory:', null, null, array (PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $this->pdo->query('create table teste(teste_id integer primary key, teste_texto text, teste_opcao boolean)');
    }

    public function testInsert()
    {
        $this->object->setPdo($this->pdo);
        $q1 = 'insert into teste (teste_id, teste_texto) values (?, ?)';
        $this->object->cols = array ('teste_id' => 1, 'teste_texto' => 'abcdef');
        $this->object->from = array ('teste');
        $stmt = $this->object->stmt();
        $this->assertEquals($q1, $stmt->queryString);
        $this->assertEquals(array ('teste_id' => 1, 'teste_texto' => 'abcdef'), $this->pdo->query('select teste_id, teste_texto from teste')->fetch(PDO::FETCH_ASSOC));
    }

    public function testInsertWithNull()
    {
        $this->object->setPdo($this->pdo);
        $q1 = 'insert into teste (teste_id, teste_texto) values (?, ?)';
        $this->object->cols = array ('teste_id' => 1, 'teste_texto' => null);
        $this->object->from = array ('teste');
        $stmt = $this->object->stmt();
        $this->assertEquals($q1, $stmt->queryString);
        $this->assertEquals(array ('teste_id' => 1, 'teste_texto' => null), $this->pdo->query('select teste_id, teste_texto from teste')->fetch(PDO::FETCH_ASSOC));
    }

    public function testInsertWithBoolean()
    {
        $this->object->setPdo($this->pdo);
        $q1 = 'insert into teste (teste_id, teste_opcao) values (?, ?)';
        $this->object->cols = array ('teste_id' => 1, 'teste_opcao' => true);
        $this->object->from = array ('teste');
        $stmt = $this->object->stmt();
        $this->assertEquals($q1, $stmt->queryString);

        $this->assertEquals(array ('teste_id' => 1, 'teste_opcao' => 1), $this->pdo->query('select teste_id, teste_opcao from teste where teste_id = 1')->fetch(PDO::FETCH_ASSOC));

        $this->object->cols = array ('teste_id' => 2, 'teste_opcao' => false);
        $stmt = $this->object->stmt();
        $this->assertEquals($q1, $stmt->queryString);
        $this->assertEquals(array ('teste_id' => 2, 'teste_opcao' => 0), $this->pdo->query('select teste_id, teste_opcao from teste where teste_id = 2')->fetch(PDO::FETCH_ASSOC));
    }

    public function testToString()
    {
        $this->object->cols = array ('a' => 1, 'b' => 2);
        $this->object->from = array ('teste');
        $expected = 'insert into teste (a, b) values (?, ?)';
        $this->assertEquals($expected, $this->object->__toString());
    }

    public function testExceptionOnInvalidStatement()
    {
        $this->object->from = array ('nonexistant');
        $this->setExpectedException('PDOException');
        $this->object->setPdo($this->pdo)->stmt();
    }

}
