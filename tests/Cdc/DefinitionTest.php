<?php

class Cdc_DefinitionTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Cdc_Definition
     */
    protected $definition;

    protected function setUp()
    {
        // Always demand string keys to simplify parsing and documentation.
        // For the same reason, the second parameter for callbacks is also required.
        // Where you would normally just use a value without bothering with keys,
        // you have to use a dummy value. Use boolean true for this purpose.
        // A value of null will remove the key in the operation cascade.
        $def = array(
            'email' => array(
                'testing_null'            => null,
                'type'                    => Cdc_Definition::TYPE_COLUMN,
                'nice'                    => true,
                'testing'                 => true,
                // this overrides the default definitions.
                // Same structure as outside.
                Cdc_Definition::OPERATION => array(
                    'default' => array(
                        'nice'                      => 'very nice',
                        Cdc_Definition::TYPE_WIDGET => array(
                            'attributes' => array(
                                'maxlength'                 => 200,
                                'required'                  => false,
                            ),
                        ),
                    ),
                ),
                Cdc_Definition::TYPE_WIDGET => array(
                    'widget'     => 'email',
                    'attributes' => array(// plain html attributes for the form renderer
                        'required'                => 'required',
                        'maxlength'               => 255,
                    ),
                ),
                Cdc_Definition::TYPE_RULE => array(
                    array('Cdc_Rule_Email', array()), // always demand the second parameter for callbacks
                    array('Cdc_Rule_Length', array(1, 255)),
                ),
            ),
            'comment' => array(
                'testing_null'            => true,
                'type'                    => Cdc_Definition::TYPE_COLUMN,
                'testing'                 => true,
                'testing_unset'           => true,
                Cdc_Definition::OPERATION => array(
                    'default' => array(
                        'testing_unset'    => null,
                    ),
                    'test_application' => array(),
                ),
                Cdc_Definition::TYPE_WIDGET => array(
                    'type' => 'textarea',
                ),
            ),
        );

        $this->definition = new Cdc_Definition($def);
    }

    public function testKeyOnlyQuery()
    {
        $this->definition->query(Cdc_Definition::TYPE_COLUMN)->byKey('testing');

        $result = $this->definition->fetch(Cdc_Definition::MODE_KEY_ONLY);
        $this->assertEquals(array('email', 'comment'), $result);
    }

    public function testValueIsOverridenByOperationSpecificSettings()
    {
        $this->definition->query(Cdc_Definition::TYPE_COLUMN)->byKey('nice');
        $result = $this->definition->fetch();
        $this->assertEquals('very nice', $result['email']['nice']);
    }

    public function testKeyIsUnsetWhenValueIsNull()
    {
        // The ambiguity for the null value was preferred over adding extra complexity,
        // like a class just for this purpose.
        $this->definition->query(Cdc_Definition::TYPE_COLUMN)->byKey('testing_null');
        $result = $this->definition->fetch(Cdc_Definition::MODE_KEY_ONLY);
        $this->assertEquals(array('comment'), $result);
    }

    public function testKeyIsUnsetWhenSettingOperationSpecificValueToNull()
    {
        // The ambiguity for the null value was preferred over adding extra complexity,
        // like a class just for this purpose.
        $this->definition->query(Cdc_Definition::TYPE_COLUMN)->byKey('testing_unset');
        $result = $this->definition->fetch(Cdc_Definition::MODE_KEY_ONLY);
        $this->assertEmpty($result);

        // This is the same condition but the null value is set to something else
        $def = $this->definition->getDefinition();

        $def['comment'][Cdc_Definition::OPERATION][DEFAULT_OPERATION]['testing_unset'] = 'foo';

        $this->definition->setDefinition($def);
        $this->definition->query(Cdc_Definition::TYPE_COLUMN)->byKey('testing_unset');

        $result = $this->definition->fetch(Cdc_Definition::MODE_KEY_ONLY);
        $this->assertEquals(array('comment'), $result);
    }

    public function testKeyWithoutOperationThrowsException()
    {
        $def = array('email' => array('teste' => 'ok', 'type'  => Cdc_Definition::TYPE_COLUMN));
        $this->definition->setDefinition($def);

        $this->setExpectedException('Cdc_Definition_Exception_NoOperationSpecified');

        $this->definition->query()->byKey('teste');
    }

    public function testUninitializedQueryThrowsException()
    {
        $def = array('email' => array('teste' => 'ok', 'type'  => Cdc_Definition::TYPE_COLUMN));
        $this->definition->setDefinition($def);

        $this->setExpectedException('Cdc_Definition_Exception_UninitializedQuery');

        $this->definition->byKey('teste')->fetch();
    }

}
