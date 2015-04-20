<?php

class Cdc_Sql_UpdateTest extends DBTestCase
{

    /**
     * @var Update
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new Cdc_Sql_Update;
        $conn = $this->getConnection();
        $pdo = $conn->getConnection();

        $this->object->setPdo($pdo);
    }

    public function testUpdate()
    {
        $this->object->getPdo()->exec('insert into test (id, body, email) values (1, \'abc\', \'def@def.com\')');
        $q1 = 'update test set body = ? where (id = ?)';
        $this->object->from = array ('test');
        $this->object->cols = array ('body' => 'xyz');
        $this->object->where = array ('id =' => 1);
        $stmt = $this->object->stmt();
        $this->assertEquals($q1, $stmt->queryString);
        $expected = array ('id' => 1, 'body' => 'xyz');
        $this->assertEquals($expected, $this->object->getPdo()->query('select id, body from test')->fetch(PDO::FETCH_ASSOC));
    }

    public function testToString()
    {
        $this->object->where = array ('a =' => 1, 'b =' => 2);
        $this->object->from = array ('teste');
        $this->object->cols = array ('c' => 5, 'd' => 6);
        $expected = 'update teste set c = ?, d = ? where ((a = ?) and (b = ?))';
        $this->assertEquals($expected, $this->object->__toString());
    }

    public function testExceptionOnInvalidStatement()
    {
        $this->object->from = array ('nonexistant');
        $this->setExpectedException('PDOException');
        $this->object->stmt();
    }

}
