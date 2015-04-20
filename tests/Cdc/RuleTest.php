<?php

class Cdc_RuleTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Cdc_Rule
     */
    protected $rule;

    /**
     *
     * @var Cdc_Definition
     */
    protected $definition;

    protected function setUp()
    {
        $def = array(
            'email' => array(
                Cdc_Definition::OPERATION => array(
                    'default' => array(),
                ),
                Cdc_Definition::TYPE_RULE => array(
                    array('Cdc_Rule_Email', array()),
                    array('Cdc_Rule_Length', array(1, 255)),
                ),
            ),
            'comment' => array(
                Cdc_Definition::OPERATION => array(
                    'default' => array(),
                ),
                Cdc_Definition::TYPE_RULE => array(
                    array('Cdc_Rule_Length', array(10, 1000)),
                ),
            ),
        );

        $this->definition = new Cdc_Definition($def);

        $this->rule = new Cdc_Rule;
    }

    public function testRuleProcessingForInvalidData()
    {
        $result = $this->definition->query(Cdc_Definition::TYPE_RULE)->fetch();

        $this->rule->setQueryResult($result);

        $row = array(
            'email'   => 'asd', // invalid email
            'comment' => 'abc', // comment too short
        );

        $r = $this->rule->invoke($row);

        $this->assertFalse($r);
    }

    public function testRuleProcessingForValidData()
    {
        $result = $this->definition->query(Cdc_Definition::TYPE_RULE)->fetch();

        $this->rule->setQueryResult($result);

        $row = array(
            'email'   => 'asd@asd.com',
            'comment' => '0123456789',
        );

        $r = $this->rule->invoke($row);

        $this->assertTrue($r);
    }

    public function testRuleOverridenByOperationSpecificSetting()
    {

        $def = $this->definition->getDefinition();

        // clear email rules
        $def['email'][Cdc_Definition::OPERATION][DEFAULT_OPERATION][Cdc_Definition::TYPE_RULE] = array(array('Cdc_Rule_Length', array(1, 10)));


        $this->definition->setDefinition($def);

        $queryResult = $this->definition->query(Cdc_Definition::TYPE_RULE)->fetch();

        $this->rule->setQueryResult($queryResult);

        $row = array(
            'email'   => 'asd', // invalid email
            'comment' => '0123456789',
        );

        $r = $this->rule->invoke($row);
        $this->assertTrue($r);

        $row['email'] = 'dfoajfoadjoajdjodfjodafjofdjoad'; // invalid length

        $r = $this->rule->invoke($row);
        $this->assertFalse($r);

        $row['email'] = '12345';

        $r = $this->rule->invoke($row);
        $this->assertTrue($r);
    }

    public function testMessages()
    {
        $result = $this->definition->query(Cdc_Definition::TYPE_RULE)->fetch();

        $this->rule->setQueryResult($result);

        $row = array(
            'email'   => 'asd', // invalid email
            'comment' => 'abc', // comment too short
        );

        $r = $this->rule->invoke($row);

        $messages = $this->rule->getMessages();

        $this->assertArrayHasKey('email', $messages);
        $this->assertArrayHasKey('comment', $messages);
    }

}
