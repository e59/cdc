<?php

class Cdc_Pdo_ContainerTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Container
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new Cdc_Pdo_Container;
    }

    public function testNoPDOException()
    {
        $this->setExpectedException('Cdc_Pdo_Exception_PDO');
        $this->object->getPdo();
    }

    public function testWrongErrmodeException()
    {
        $this->setExpectedException('Cdc_Pdo_Exception_Errmode');
        $this->object->setPdo(new PDO('sqlite::memory:'));
    }

    public function testPDO()
    {
        $this->object->setPdo(new PDO('sqlite::memory:', null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)));
        $pdo = $this->object->getPdo();
        $this->assertTrue($pdo instanceof PDO);
    }

}

