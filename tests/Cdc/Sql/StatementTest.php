<?php

class Cdc_Sql_StatementTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Cdc_Sql_Statement
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new Cdc_Sql_Statement;
    }

    public function testHaving()
    {
        $having = $this->object->buildWhere(array ('x =' => 'y'), $values, 'having');
        $this->assertEquals(' having (x = ?)', $having);
    }

    public function testCriterion()
    {
        $where = $this->object->buildWhere(array ('x =' => 'y'), $values);
        $this->assertEquals(' where (x = ?)', $where);
        $this->assertEquals(array ('y'), $values);
    }

    public function testClauseWithoutOperatorAnd2Criteria()
    {
        $where = $this->object->buildWhere(array ('a =' => 1, 'b =' => 2), $values);
        $this->assertEquals(' where ((a = ?) and (b = ?))', $where);
        $this->assertEquals(array (1, 2), $values);
    }

    public function testClauseWithoutOperatorAnd3Criteria()
    {
        $where = $this->object->buildWhere(array ('a =' => 1, 'b =' => 2, 'c =' => 3), $values);
        $this->assertEquals(' where ((a = ?) and (b = ?) and (c = ?))', $where);
        $this->assertEquals(array (1, 2, 3), $values);
    }

    public function testClauseWithoutOperatorAnd4Criteria()
    {
        $where = $this->object->buildWhere(array ('a =' => 1, 'b =' => 2, 'c =' => 3, 'd =' => 4), $values);
        $this->assertEquals(' where ((a = ?) and (b = ?) and (c = ?) and (d = ?))', $where);
        $this->assertEquals(array (1, 2, 3, 4), $values);
    }

    public function testClauseWithoutOperatorAnd5Criteria()
    {
        $where = $this->object->buildWhere(array ('a =' => 1, 'b =' => 2, 'c =' => 3, 'd =' => 4, 'e = 5'), $values);
        $this->assertEquals(' where ((a = ?) and (b = ?) and (c = ?) and (d = ?) and (e = 5))', $where);
        $this->assertEquals(array (1, 2, 3, 4), $values);
    }

    public function testClauseWithSingleAndOperator()
    {
        $where = $this->object->buildWhere(array ('and' => array ('a =' => 1, 'b =' => 2)), $values);
        $this->assertEquals(' where ((a = ?) and (b = ?))', $where);
        $this->assertEquals(array (1, 2), $values);
    }

    public function testClauseWithSingleOrOperator()
    {
        $where = $this->object->buildWhere(array ('or' => array ('a =' => 1, 'b = 2')), $values);
        $this->assertEquals(' where ((a = ?) or (b = 2))', $where);
        $this->assertEquals(array (1), $values);
    }

    public function testBigClause()
    {
        $where = $this->object->buildWhere(array ('a =' => 1, 'or' => array ('b =' => 2, array ('x =' => 3, 'y =' => 1000)), 'd =' => 4, 'e =' => 5), $values);
        $this->assertEquals(' where ((a = ?) and ((b = ?) or ((x = ?) and (y = ?))) and (d = ?) and (e = ?))', $where);
        $expectedValues = array (1, 2, 3, 1000, 4, 5);
        $this->assertEquals($expectedValues, $values);
        $this->assertEquals(array_values($expectedValues) === array_values($values), true);
        $this->assertEquals(array_keys($expectedValues) === array_keys($values), true);
    }

    public function testArrayParam()
    {
        $arrayParam = array (1, 2, 3, 4);
        $where = $this->object->buildWhere(array ('a in' => $arrayParam), $values);
        $this->assertEquals(' where (a in (?, ?, ?, ?))', $where);

        $this->assertEquals(array_values($arrayParam) === array_values($values), true);
    }

    public function testNullParam()
    {
        $arrayParam = array ('a is' => null, 'b is null');
        $where = $this->object->buildWhere($arrayParam, $values);
        $this->assertEquals(' where (b is null)', $where);
    }

    public function testFixed()
    {
        $arrayParam = array ('b is null', 'or' => array ('a is not null', 'c = 5'));
        $where = $this->object->buildWhere($arrayParam, $values);
        $this->assertEquals(' where ((b is null) and ((a is not null) or (c = 5)))', $where);
    }

}
