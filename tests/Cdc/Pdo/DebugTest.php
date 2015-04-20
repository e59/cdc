<?php

class Cdc_Pdo_DebugTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Debug
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new Cdc_Pdo_Debug('sqlite::memory:', null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    }

    public function testLogging()
    {
        $q1 = 'create table teste(teste_id integer primary key, teste_texto text)';
        $this->object->query($q1);
        $q2 = 'insert into teste values (1, "abcdef")';
        $this->object->query($q2);
        $q3 = 'select * from teste limit 1';
        $row = $this->object->query($q3)->fetch(PDO::FETCH_ASSOC);

        $loggerIndex = 0;

        $this->assertEquals($q1, $this->object->logger->log[$loggerIndex++]['sql']);
        $this->assertEquals($q2, $this->object->logger->log[$loggerIndex++]['sql']);
        $this->assertEquals($q3, $this->object->logger->log[$loggerIndex++]['sql']);
        $this->assertEquals(array('teste_id' => 1, 'teste_texto' => 'abcdef'), $row);

        $q4 = 'insert into teste values (?, "abcdef")';
        $stmt = $this->object->prepare($q4);

        for ($i = 2; $i < 10; $i++)
        {
            $stmt->bindValue(1, $i);
            $stmt->execute();
            $this->assertEquals($q4, $this->object->logger->log[$loggerIndex++]['sql']);
        }

        $this->object->beginTransaction();
        $q5 = 'delete from teste where teste_id = 2';
        $this->object->exec($q5);
        $this->object->rollback();

        $this->assertEquals('begin transaction', $this->object->logger->log[$loggerIndex++]['sql']);
        $this->assertEquals($q5, $this->object->logger->log[$loggerIndex++]['sql']);
        $this->assertEquals('rollBack', $this->object->logger->log[$loggerIndex++]['sql']);


        $this->object->beginTransaction();
        $this->object->exec($q5);
        $this->object->commit();

        $this->assertEquals('begin transaction', $this->object->logger->log[$loggerIndex++]['sql']);
        $this->assertEquals($q5, $this->object->logger->log[$loggerIndex++]['sql']);
        $this->assertEquals('commit', $this->object->logger->log[$loggerIndex++]['sql']);
    }

}
