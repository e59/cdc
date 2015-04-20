<?php

class Cdc_Sql_DeleteTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Cdc_Sql_Delete
     */
    protected $object;
    protected $pdo;

    protected function setUp()
    {
        $this->object = new Cdc_Sql_Delete;
        $this->pdo = new PDO('sqlite::memory:', null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $this->pdo->query('create table teste(teste_id integer primary key, teste_texto text, teste_opcao boolean)');
        $this->pdo->exec('insert into teste (teste_id, teste_texto) values (1, "abcdef")');
    }

    public function testDelete()
    {
        $q1 = 'delete from teste where (teste_id = ?)';
        $this->object->setPdo($this->pdo);
        $this->object->from = array('teste');
        $this->object->where = array('teste_id =' => 1);
        $stmt        = $this->object->stmt();
        $this->assertEquals($q1, $stmt->queryString);
        $this->assertFalse($this->pdo->query('select teste_id, teste_texto from teste')->fetch(PDO::FETCH_ASSOC));
    }

    public function testToString()
    {
        $this->object->where = array('a =' => 1, 'b =' => 2);
        $this->object->from = array('teste');
        $expected = 'delete from teste where ((a = ?) and (b = ?))';
        $this->assertEquals($expected, $this->object->__toString());
    }

    public function testExceptionOnInvalidStatement()
    {
        $this->object->from = array('nonexistant');
        $this->setExpectedException('PDOException');
        $this->object->setPdo($this->pdo)->stmt();
    }

}
