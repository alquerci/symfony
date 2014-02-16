<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Tests_Extension_Validator_ViolationMapper_ViolationMapperTest extends PHPUnit_Framework_TestCase
{
    const LEVEL_0 = 0;

    const LEVEL_1 = 1;

    const LEVEL_1B = 2;

    const LEVEL_2 = 3;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $dispatcher;

    /**
     * @var Symfony_Component_Form_Extension_Validator_ViolationMapper_ViolationMapper
     */
    private $mapper;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $messageTemplate;

    /**
     * @var array
     */
    private $params;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_Event')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        $this->dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $this->mapper = new Symfony_Component_Form_Extension_Validator_ViolationMapper_ViolationMapper();
        $this->message = 'Message';
        $this->messageTemplate = 'Message template';
        $this->params = array('foo' => 'bar');
    }

    protected function getForm($name = 'name', $propertyPath = null, $dataClass = null, $errorMapping = array(), $virtual = false, $synchronized = true)
    {
        $config = new Symfony_Component_Form_FormConfigBuilder($name, $dataClass, $this->dispatcher, array(
            'error_mapping' => $errorMapping,
        ));
        $config->setMapped(true);
        $config->setVirtual($virtual);
        $config->setPropertyPath($propertyPath);
        $config->setCompound(true);
        $config->setDataMapper($this->getDataMapper());

        if (!$synchronized) {
            $config->addViewTransformer(new Symfony_Component_Form_CallbackTransformer(
                create_function('$normData', 'return $normData;'),
                create_function('', 'throw new Symfony_Component_Form_Exception_TransformationFailedException();')
            ));
        }

        return new Symfony_Component_Form_Form($config);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getDataMapper()
    {
        return $this->getMock('Symfony_Component_Form_DataMapperInterface');
    }

    /**
     * @param $propertyPath
     *
     * @return Symfony_Component_Validator_ConstraintViolation
     */
    protected function getConstraintViolation($propertyPath)
    {
        return new Symfony_Component_Validator_ConstraintViolation($this->message, $this->messageTemplate, $this->params, null, $propertyPath, null);
    }

    /**
     * @return Symfony_Component_Form_FormError
     */
    protected function getFormError()
    {
        return new Symfony_Component_Form_FormError($this->message, $this->messageTemplate, $this->params);
    }

    public function testMapToVirtualFormIfDataDoesNotMatch()
    {
        $violation = $this->getConstraintViolation('children[address].data.foo');
        $parent = $this->getForm('parent');
        $child = $this->getForm('address', 'address', null, array(), true);
        $grandChild = $this->getForm('street');

        $parent->add($child);
        $child->add($grandChild);

        $this->mapper->mapViolation($violation, $parent);

        $this->assertCount(0, $parent->getErrors(), $parent->getName() . ' should not have an error, but has one');
        $this->assertEquals(array($this->getFormError()), $child->getErrors(), $child->getName() . ' should have an error, but has none');
        $this->assertCount(0, $grandChild->getErrors(), $grandChild->getName() . ' should not have an error, but has one');
    }

    public function testFollowDotRules()
    {
        $violation = $this->getConstraintViolation('data.foo');
        $parent = $this->getForm('parent', null, null, array(
            'foo' => 'address',
        ));
        $child = $this->getForm('address', null, null, array(
            '.' => 'street',
        ));
        $grandChild = $this->getForm('street', null, null, array(
            '.' => 'name',
        ));
        $grandGrandChild = $this->getForm('name');

        $parent->add($child);
        $child->add($grandChild);
        $grandChild->add($grandGrandChild);

        $this->mapper->mapViolation($violation, $parent);

        $this->assertCount(0, $parent->getErrors(), $parent->getName() . ' should not have an error, but has one');
        $this->assertCount(0, $child->getErrors(), $child->getName() . ' should not have an error, but has one');
        $this->assertCount(0, $grandChild->getErrors(), $grandChild->getName() . ' should not have an error, but has one');
        $this->assertEquals(array($this->getFormError()), $grandGrandChild->getErrors(), $grandGrandChild->getName() . ' should have an error, but has none');
    }

    public function testAbortMappingIfNotSynchronized()
    {
        $violation = $this->getConstraintViolation('children[address].data.street');
        $parent = $this->getForm('parent');
        $child = $this->getForm('address', 'address', null, array(), false, false);
        // even though "street" is synchronized, it should not have any errors
        // due to its parent not being synchronized
        $grandChild = $this->getForm('street' , 'street');

        $parent->add($child);
        $child->add($grandChild);

        // bind to invoke the transformer and mark the form unsynchronized
        $parent->bind(array());

        $this->mapper->mapViolation($violation, $parent);

        $this->assertCount(0, $parent->getErrors(), $parent->getName() . ' should not have an error, but has one');
        $this->assertCount(0, $child->getErrors(), $child->getName() . ' should not have an error, but has one');
        $this->assertCount(0, $grandChild->getErrors(), $grandChild->getName() . ' should not have an error, but has one');
    }

    public function testAbortVirtualFormMappingIfNotSynchronized()
    {
        $violation = $this->getConstraintViolation('children[address].children[street].data.foo');
        $parent = $this->getForm('parent');
        $child = $this->getForm('address', 'address', null, array(), true, false);
        // even though "street" is synchronized, it should not have any errors
        // due to its parent not being synchronized
        $grandChild = $this->getForm('street' , 'street', null, array(), true);

        $parent->add($child);
        $child->add($grandChild);

        // bind to invoke the transformer and mark the form unsynchronized
        $parent->bind(array());

        $this->mapper->mapViolation($violation, $parent);

        $this->assertCount(0, $parent->getErrors(), $parent->getName() . ' should not have an error, but has one');
        $this->assertCount(0, $child->getErrors(), $child->getName() . ' should not have an error, but has one');
        $this->assertCount(0, $grandChild->getErrors(), $grandChild->getName() . ' should not have an error, but has one');
    }

    public function testAbortDotRuleMappingIfNotSynchronized()
    {
        $violation = $this->getConstraintViolation('data.address');
        $parent = $this->getForm('parent');
        $child = $this->getForm('address', 'address', null, array(
            '.' => 'street',
        ), false, false);
        // even though "street" is synchronized, it should not have any errors
        // due to its parent not being synchronized
        $grandChild = $this->getForm('street');

        $parent->add($child);
        $child->add($grandChild);

        // bind to invoke the transformer and mark the form unsynchronized
        $parent->bind(array());

        $this->mapper->mapViolation($violation, $parent);

        $this->assertCount(0, $parent->getErrors(), $parent->getName() . ' should not have an error, but has one');
        $this->assertCount(0, $child->getErrors(), $child->getName() . ' should not have an error, but has one');
        $this->assertCount(0, $grandChild->getErrors(), $grandChild->getName() . ' should not have an error, but has one');
    }

    public function provideDefaultTests()
    {
        // The mapping must be deterministic! If a child has the property path "[street]",
        // "data[street]" should be mapped, but "data.street" should not!
        return array(
            // mapping target, child name, its property path, grand child name, its property path, violation path
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', ''),
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', 'data'),

            array(self::LEVEL_2, 'address', 'address', 'street', 'street', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'street', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'street', 'children[address].data'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'street', 'children[address].data.street.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'street', 'children[address].data[street].prop'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'street', 'data.address.street'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'street', 'data.address.street.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'street', 'data.address[street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'street', 'data.address[street].prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', 'data[address].street'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', 'data[address].street.prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', 'data[address][street]'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', 'data[address][street].prop'),

            array(self::LEVEL_2, 'address', 'address', 'street', '[street]', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', 'address', 'street', '[street]', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[street]', 'children[address].data'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[street]', 'children[address].data.street.prop'),
            array(self::LEVEL_2, 'address', 'address', 'street', '[street]', 'children[address].data[street]'),
            array(self::LEVEL_2, 'address', 'address', 'street', '[street]', 'children[address].data[street].prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[street]', 'data.address.street'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[street]', 'data.address.street.prop'),
            array(self::LEVEL_2, 'address', 'address', 'street', '[street]', 'data.address[street]'),
            array(self::LEVEL_2, 'address', 'address', 'street', '[street]', 'data.address[street].prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[street]', 'data[address].street'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[street]', 'data[address].street.prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[street]', 'data[address][street]'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[street]', 'data[address][street].prop'),

            array(self::LEVEL_2, 'address', '[address]', 'street', 'street', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', '[address]', 'street', 'street', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'street', 'children[address].data'),
            array(self::LEVEL_2, 'address', '[address]', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_2, 'address', '[address]', 'street', 'street', 'children[address].data.street.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'street', 'children[address].data[street].prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'street', 'data.address.street'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'street', 'data.address.street.prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'street', 'data.address[street]'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'street', 'data.address[street].prop'),
            array(self::LEVEL_2, 'address', '[address]', 'street', 'street', 'data[address].street'),
            array(self::LEVEL_2, 'address', '[address]', 'street', 'street', 'data[address].street.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'street', 'data[address][street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'street', 'data[address][street].prop'),

            array(self::LEVEL_2, 'address', '[address]', 'street', '[street]', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', '[address]', 'street', '[street]', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[street]', 'children[address].data'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[street]', 'children[address].data.street.prop'),
            array(self::LEVEL_2, 'address', '[address]', 'street', '[street]', 'children[address].data[street]'),
            array(self::LEVEL_2, 'address', '[address]', 'street', '[street]', 'children[address].data[street].prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[street]', 'data.address.street'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[street]', 'data.address.street.prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[street]', 'data.address[street]'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[street]', 'data.address[street].prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[street]', 'data[address].street'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[street]', 'data[address].street.prop'),
            array(self::LEVEL_2, 'address', '[address]', 'street', '[street]', 'data[address][street]'),
            array(self::LEVEL_2, 'address', '[address]', 'street', '[street]', 'data[address][street].prop'),

            array(self::LEVEL_2, 'address', 'person.address', 'street', 'street', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', 'person.address', 'street', 'street', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', 'person.address', 'street', 'street', 'children[address].data'),
            array(self::LEVEL_2, 'address', 'person.address', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_2, 'address', 'person.address', 'street', 'street', 'children[address].data.street.prop'),
            array(self::LEVEL_1, 'address', 'person.address', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_1, 'address', 'person.address', 'street', 'street', 'children[address].data[street].prop'),
            array(self::LEVEL_2, 'address', 'person.address', 'street', 'street', 'data.person.address.street'),
            array(self::LEVEL_2, 'address', 'person.address', 'street', 'street', 'data.person.address.street.prop'),
            array(self::LEVEL_1, 'address', 'person.address', 'street', 'street', 'data.person.address[street]'),
            array(self::LEVEL_1, 'address', 'person.address', 'street', 'street', 'data.person.address[street].prop'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'data.person[address].street'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'data.person[address].street.prop'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'data.person[address][street]'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'data.person[address][street].prop'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'data[person].address.street'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'data[person].address.street.prop'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'data[person].address[street]'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'data[person].address[street].prop'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'data[person][address].street'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'data[person][address].street.prop'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'data[person][address][street]'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'data[person][address][street].prop'),

            array(self::LEVEL_2, 'address', 'person.address', 'street', '[street]', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', 'person.address', 'street', '[street]', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', 'person.address', 'street', '[street]', 'children[address].data'),
            array(self::LEVEL_1, 'address', 'person.address', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_1, 'address', 'person.address', 'street', '[street]', 'children[address].data.street.prop'),
            array(self::LEVEL_2, 'address', 'person.address', 'street', '[street]', 'children[address].data[street]'),
            array(self::LEVEL_2, 'address', 'person.address', 'street', '[street]', 'children[address].data[street].prop'),
            array(self::LEVEL_1, 'address', 'person.address', 'street', '[street]', 'data.person.address.street'),
            array(self::LEVEL_1, 'address', 'person.address', 'street', '[street]', 'data.person.address.street.prop'),
            array(self::LEVEL_2, 'address', 'person.address', 'street', '[street]', 'data.person.address[street]'),
            array(self::LEVEL_2, 'address', 'person.address', 'street', '[street]', 'data.person.address[street].prop'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', '[street]', 'data.person[address].street'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', '[street]', 'data.person[address].street.prop'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', '[street]', 'data.person[address][street]'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', '[street]', 'data.person[address][street].prop'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', '[street]', 'data[person].address.street'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', '[street]', 'data[person].address.street.prop'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', '[street]', 'data[person].address[street]'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', '[street]', 'data[person].address[street].prop'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', '[street]', 'data[person][address].street'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', '[street]', 'data[person][address].street.prop'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', '[street]', 'data[person][address][street]'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', '[street]', 'data[person][address][street].prop'),

            array(self::LEVEL_2, 'address', 'person[address]', 'street', 'street', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', 'person[address]', 'street', 'street', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', 'person[address]', 'street', 'street', 'children[address].data'),
            array(self::LEVEL_2, 'address', 'person[address]', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_2, 'address', 'person[address]', 'street', 'street', 'children[address].data.street.prop'),
            array(self::LEVEL_1, 'address', 'person[address]', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_1, 'address', 'person[address]', 'street', 'street', 'children[address].data[street].prop'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', 'street', 'data.person.address.street'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', 'street', 'data.person.address.street.prop'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', 'street', 'data.person.address[street]'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', 'street', 'data.person.address[street].prop'),
            array(self::LEVEL_2, 'address', 'person[address]', 'street', 'street', 'data.person[address].street'),
            array(self::LEVEL_2, 'address', 'person[address]', 'street', 'street', 'data.person[address].street.prop'),
            array(self::LEVEL_1, 'address', 'person[address]', 'street', 'street', 'data.person[address][street]'),
            array(self::LEVEL_1, 'address', 'person[address]', 'street', 'street', 'data.person[address][street].prop'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', 'street', 'data[person].address.street'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', 'street', 'data[person].address.street.prop'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', 'street', 'data[person].address[street]'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', 'street', 'data[person].address[street].prop'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', 'street', 'data[person][address].street'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', 'street', 'data[person][address].street.prop'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', 'street', 'data[person][address][street]'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', 'street', 'data[person][address][street].prop'),

            array(self::LEVEL_2, 'address', 'person[address]', 'street', '[street]', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', 'person[address]', 'street', '[street]', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', 'person[address]', 'street', '[street]', 'children[address].data'),
            array(self::LEVEL_1, 'address', 'person[address]', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_1, 'address', 'person[address]', 'street', '[street]', 'children[address].data.street.prop'),
            array(self::LEVEL_2, 'address', 'person[address]', 'street', '[street]', 'children[address].data[street]'),
            array(self::LEVEL_2, 'address', 'person[address]', 'street', '[street]', 'children[address].data[street].prop'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', '[street]', 'data.person.address.street'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', '[street]', 'data.person.address.street.prop'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', '[street]', 'data.person.address[street]'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', '[street]', 'data.person.address[street].prop'),
            array(self::LEVEL_1, 'address', 'person[address]', 'street', '[street]', 'data.person[address].street'),
            array(self::LEVEL_1, 'address', 'person[address]', 'street', '[street]', 'data.person[address].street.prop'),
            array(self::LEVEL_2, 'address', 'person[address]', 'street', '[street]', 'data.person[address][street]'),
            array(self::LEVEL_2, 'address', 'person[address]', 'street', '[street]', 'data.person[address][street].prop'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', '[street]', 'data[person].address.street'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', '[street]', 'data[person].address.street.prop'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', '[street]', 'data[person].address[street]'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', '[street]', 'data[person].address[street].prop'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', '[street]', 'data[person][address].street'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', '[street]', 'data[person][address].street.prop'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', '[street]', 'data[person][address][street]'),
            array(self::LEVEL_0, 'address', 'person[address]', 'street', '[street]', 'data[person][address][street].prop'),

            array(self::LEVEL_2, 'address', '[person].address', 'street', 'street', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', '[person].address', 'street', 'street', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', '[person].address', 'street', 'street', 'children[address].data'),
            array(self::LEVEL_2, 'address', '[person].address', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_2, 'address', '[person].address', 'street', 'street', 'children[address].data.street.prop'),
            array(self::LEVEL_1, 'address', '[person].address', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_1, 'address', '[person].address', 'street', 'street', 'children[address].data[street].prop'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', 'street', 'data.person.address.street'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', 'street', 'data.person.address.street.prop'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', 'street', 'data.person.address[street]'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', 'street', 'data.person.address[street].prop'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', 'street', 'data.person[address].street'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', 'street', 'data.person[address].street.prop'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', 'street', 'data.person[address][street]'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', 'street', 'data.person[address][street].prop'),
            array(self::LEVEL_2, 'address', '[person].address', 'street', 'street', 'data[person].address.street'),
            array(self::LEVEL_2, 'address', '[person].address', 'street', 'street', 'data[person].address.street.prop'),
            array(self::LEVEL_1, 'address', '[person].address', 'street', 'street', 'data[person].address[street]'),
            array(self::LEVEL_1, 'address', '[person].address', 'street', 'street', 'data[person].address[street].prop'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', 'street', 'data[person][address].street'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', 'street', 'data[person][address].street.prop'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', 'street', 'data[person][address][street]'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', 'street', 'data[person][address][street].prop'),

            array(self::LEVEL_2, 'address', '[person].address', 'street', '[street]', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', '[person].address', 'street', '[street]', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', '[person].address', 'street', '[street]', 'children[address].data'),
            array(self::LEVEL_1, 'address', '[person].address', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_1, 'address', '[person].address', 'street', '[street]', 'children[address].data.street.prop'),
            array(self::LEVEL_2, 'address', '[person].address', 'street', '[street]', 'children[address].data[street]'),
            array(self::LEVEL_2, 'address', '[person].address', 'street', '[street]', 'children[address].data[street].prop'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', '[street]', 'data.person.address.street'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', '[street]', 'data.person.address.street.prop'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', '[street]', 'data.person.address[street]'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', '[street]', 'data.person.address[street].prop'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', '[street]', 'data.person[address].street'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', '[street]', 'data.person[address].street.prop'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', '[street]', 'data.person[address][street]'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', '[street]', 'data.person[address][street].prop'),
            array(self::LEVEL_1, 'address', '[person].address', 'street', '[street]', 'data[person].address.street'),
            array(self::LEVEL_1, 'address', '[person].address', 'street', '[street]', 'data[person].address.street.prop'),
            array(self::LEVEL_2, 'address', '[person].address', 'street', '[street]', 'data[person].address[street]'),
            array(self::LEVEL_2, 'address', '[person].address', 'street', '[street]', 'data[person].address[street].prop'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', '[street]', 'data[person][address].street'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', '[street]', 'data[person][address].street.prop'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', '[street]', 'data[person][address][street]'),
            array(self::LEVEL_0, 'address', '[person].address', 'street', '[street]', 'data[person][address][street].prop'),

            array(self::LEVEL_2, 'address', '[person][address]', 'street', 'street', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', '[person][address]', 'street', 'street', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', '[person][address]', 'street', 'street', 'children[address]'),
            array(self::LEVEL_1, 'address', '[person][address]', 'street', 'street', 'children[address].data'),
            array(self::LEVEL_2, 'address', '[person][address]', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_2, 'address', '[person][address]', 'street', 'street', 'children[address].data.street.prop'),
            array(self::LEVEL_1, 'address', '[person][address]', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_1, 'address', '[person][address]', 'street', 'street', 'children[address].data[street].prop'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', 'street', 'data.person.address.street'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', 'street', 'data.person.address.street.prop'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', 'street', 'data.person.address[street]'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', 'street', 'data.person.address[street].prop'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', 'street', 'data.person[address].street'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', 'street', 'data.person[address].street.prop'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', 'street', 'data.person[address][street]'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', 'street', 'data.person[address][street].prop'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', 'street', 'data[person].address.street'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', 'street', 'data[person].address.street.prop'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', 'street', 'data[person].address[street]'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', 'street', 'data[person].address[street].prop'),
            array(self::LEVEL_2, 'address', '[person][address]', 'street', 'street', 'data[person][address].street'),
            array(self::LEVEL_2, 'address', '[person][address]', 'street', 'street', 'data[person][address].street.prop'),
            array(self::LEVEL_1, 'address', '[person][address]', 'street', 'street', 'data[person][address][street]'),
            array(self::LEVEL_1, 'address', '[person][address]', 'street', 'street', 'data[person][address][street].prop'),

            array(self::LEVEL_2, 'address', '[person][address]', 'street', '[street]', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', '[person][address]', 'street', '[street]', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', '[person][address]', 'street', '[street]', 'children[address].data'),
            array(self::LEVEL_1, 'address', '[person][address]', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_1, 'address', '[person][address]', 'street', '[street]', 'children[address].data.street.prop'),
            array(self::LEVEL_2, 'address', '[person][address]', 'street', '[street]', 'children[address].data[street]'),
            array(self::LEVEL_2, 'address', '[person][address]', 'street', '[street]', 'children[address].data[street].prop'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', '[street]', 'data.person.address.street'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', '[street]', 'data.person.address.street.prop'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', '[street]', 'data.person.address[street]'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', '[street]', 'data.person.address[street].prop'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', '[street]', 'data.person[address].street'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', '[street]', 'data.person[address].street.prop'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', '[street]', 'data.person[address][street]'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', '[street]', 'data.person[address][street].prop'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', '[street]', 'data[person].address.street'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', '[street]', 'data[person].address.street.prop'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', '[street]', 'data[person].address[street]'),
            array(self::LEVEL_0, 'address', '[person][address]', 'street', '[street]', 'data[person].address[street].prop'),
            array(self::LEVEL_1, 'address', '[person][address]', 'street', '[street]', 'data[person][address].street'),
            array(self::LEVEL_1, 'address', '[person][address]', 'street', '[street]', 'data[person][address].street.prop'),
            array(self::LEVEL_2, 'address', '[person][address]', 'street', '[street]', 'data[person][address][street]'),
            array(self::LEVEL_2, 'address', '[person][address]', 'street', '[street]', 'data[person][address][street].prop'),

            array(self::LEVEL_2, 'address', 'address', 'street', 'office.street', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'office.street', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'children[address].data'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'children[address].data.office'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'office.street', 'children[address].data.office.street'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'office.street', 'children[address].data.office.street.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'children[address].data.office[street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'children[address].data.office[street].prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'children[address].data[office]'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'children[address].data[office].street'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'children[address].data[office].street.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'children[address].data[office][street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'children[address].data[office][street].prop'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'office.street', 'data.address.office.street'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'office.street', 'data.address.office.street.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'data.address.office[street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'data.address.office[street].prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'data.address[office].street'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'data.address[office].street.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'data.address[office][street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'data.address[office][street].prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office.street', 'data[address].office.street'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office.street', 'data[address].office.street.prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office.street', 'data[address].office[street]'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office.street', 'data[address].office[street].prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office.street', 'data[address][office].street'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office.street', 'data[address][office].street.prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office.street', 'data[address][office][street]'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office.street', 'data[address][office][street].prop'),

            array(self::LEVEL_2, 'address', '[address]', 'street', 'office.street', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', '[address]', 'street', 'office.street', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office.street', 'children[address].data'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office.street', 'children[address].data.office'),
            array(self::LEVEL_2, 'address', '[address]', 'street', 'office.street', 'children[address].data.office.street'),
            array(self::LEVEL_2, 'address', '[address]', 'street', 'office.street', 'children[address].data.office.street.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office.street', 'children[address].data.office[street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office.street', 'children[address].data.office[street].prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office.street', 'children[address].data[office]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office.street', 'children[address].data[office].street'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office.street', 'children[address].data[office].street.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office.street', 'children[address].data[office][street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office.street', 'children[address].data[office][street].prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office.street', 'data.address.office.street'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office.street', 'data.address.office.street.prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office.street', 'data.address.office[street]'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office.street', 'data.address.office[street].prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office.street', 'data.address[office].street'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office.street', 'data.address[office].street.prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office.street', 'data.address[office][street]'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office.street', 'data.address[office][street].prop'),
            array(self::LEVEL_2, 'address', '[address]', 'street', 'office.street', 'data[address].office.street'),
            array(self::LEVEL_2, 'address', '[address]', 'street', 'office.street', 'data[address].office.street.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office.street', 'data[address].office[street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office.street', 'data[address].office[street].prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office.street', 'data[address][office].street'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office.street', 'data[address][office].street.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office.street', 'data[address][office][street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office.street', 'data[address][office][street].prop'),

            array(self::LEVEL_2, 'address', 'address', 'street', 'office[street]', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'office[street]', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office[street]', 'children[address].data'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office[street]', 'children[address].data.office'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office[street]', 'children[address].data.office.street'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office[street]', 'children[address].data.office.street.prop'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'office[street]', 'children[address].data.office[street]'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'office[street]', 'children[address].data.office[street].prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office[street]', 'children[address].data[office]'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office[street]', 'children[address].data[office].street'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office[street]', 'children[address].data[office].street.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office[street]', 'children[address].data[office][street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office[street]', 'children[address].data[office][street].prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office[street]', 'data.address.office.street'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office[street]', 'data.address.office.street.prop'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'office[street]', 'data.address.office[street]'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'office[street]', 'data.address.office[street].prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office[street]', 'data.address[office].street'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office[street]', 'data.address[office].street.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office[street]', 'data.address[office][street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office[street]', 'data.address[office][street].prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office[street]', 'data[address].office.street'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office[street]', 'data[address].office.street.prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office[street]', 'data[address].office[street]'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office[street]', 'data[address].office[street].prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office[street]', 'data[address][office].street'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office[street]', 'data[address][office].street.prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office[street]', 'data[address][office][street]'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'office[street]', 'data[address][office][street].prop'),

            array(self::LEVEL_2, 'address', '[address]', 'street', 'office[street]', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', '[address]', 'street', 'office[street]', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office[street]', 'children[address].data.office.street'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office[street]', 'children[address].data.office.street.prop'),
            array(self::LEVEL_2, 'address', '[address]', 'street', 'office[street]', 'children[address].data.office[street]'),
            array(self::LEVEL_2, 'address', '[address]', 'street', 'office[street]', 'children[address].data.office[street].prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office[street]', 'children[address].data[office]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office[street]', 'children[address].data[office].street'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office[street]', 'children[address].data[office].street.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office[street]', 'children[address].data[office][street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office[street]', 'children[address].data[office][street].prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office[street]', 'data.address.office.street'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office[street]', 'data.address.office.street.prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office[street]', 'data.address.office[street]'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office[street]', 'data.address.office[street].prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office[street]', 'data.address[office].street'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office[street]', 'data.address[office].street.prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office[street]', 'data.address[office][street]'),
            array(self::LEVEL_0, 'address', '[address]', 'street', 'office[street]', 'data.address[office][street].prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office[street]', 'data[address].office.street'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office[street]', 'data[address].office.street.prop'),
            array(self::LEVEL_2, 'address', '[address]', 'street', 'office[street]', 'data[address].office[street]'),
            array(self::LEVEL_2, 'address', '[address]', 'street', 'office[street]', 'data[address].office[street].prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office[street]', 'data[address][office].street'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office[street]', 'data[address][office].street.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office[street]', 'data[address][office][street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'office[street]', 'data[address][office][street].prop'),

            array(self::LEVEL_2, 'address', 'address', 'street', '[office].street', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', 'address', 'street', '[office].street', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office].street', 'children[address].data'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office].street', 'children[address].data.office'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office].street', 'children[address].data.office.street'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office].street', 'children[address].data.office.street.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office].street', 'children[address].data.office[street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office].street', 'children[address].data.office[street].prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office].street', 'children[address].data[office]'),
            array(self::LEVEL_2, 'address', 'address', 'street', '[office].street', 'children[address].data[office].street'),
            array(self::LEVEL_2, 'address', 'address', 'street', '[office].street', 'children[address].data[office].street.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office].street', 'children[address].data[office][street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office].street', 'children[address].data[office][street].prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office].street', 'data.address.office.street'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office].street', 'data.address.office.street.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office].street', 'data.address.office[street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office].street', 'data.address.office[street].prop'),
            array(self::LEVEL_2, 'address', 'address', 'street', '[office].street', 'data.address[office].street'),
            array(self::LEVEL_2, 'address', 'address', 'street', '[office].street', 'data.address[office].street.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office].street', 'data.address[office][street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office].street', 'data.address[office][street].prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office].street', 'data[address].office.street'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office].street', 'data[address].office.street.prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office].street', 'data[address].office[street]'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office].street', 'data[address].office[street].prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office].street', 'data[address][office].street'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office].street', 'data[address][office].street.prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office].street', 'data[address][office][street]'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office].street', 'data[address][office][street].prop'),

            array(self::LEVEL_2, 'address', '[address]', 'street', '[office].street', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', '[address]', 'street', '[office].street', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office].street', 'children[address].data'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office].street', 'children[address].data.office'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office].street', 'children[address].data.office.street'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office].street', 'children[address].data.office.street.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office].street', 'children[address].data.office[street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office].street', 'children[address].data.office[street].prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office].street', 'children[address].data[office]'),
            array(self::LEVEL_2, 'address', '[address]', 'street', '[office].street', 'children[address].data[office].street'),
            array(self::LEVEL_2, 'address', '[address]', 'street', '[office].street', 'children[address].data[office].street.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office].street', 'children[address].data[office][street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office].street', 'children[address].data[office][street].prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office].street', 'data.address.office.street'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office].street', 'data.address.office.street.prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office].street', 'data.address.office[street]'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office].street', 'data.address.office[street].prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office].street', 'data.address[office].street'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office].street', 'data.address[office].street.prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office].street', 'data.address[office][street]'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office].street', 'data.address[office][street].prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office].street', 'data[address].office.street'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office].street', 'data[address].office.street.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office].street', 'data[address].office[street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office].street', 'data[address].office[street].prop'),
            array(self::LEVEL_2, 'address', '[address]', 'street', '[office].street', 'data[address][office].street'),
            array(self::LEVEL_2, 'address', '[address]', 'street', '[office].street', 'data[address][office].street.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office].street', 'data[address][office][street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office].street', 'data[address][office][street].prop'),

            array(self::LEVEL_2, 'address', 'address', 'street', '[office][street]', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', 'address', 'street', '[office][street]', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office][street]', 'children[address].data'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office][street]', 'children[address].data.office'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office][street]', 'children[address].data.office.street'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office][street]', 'children[address].data.office.street.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office][street]', 'children[address].data.office[street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office][street]', 'children[address].data.office[street].prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office][street]', 'children[address].data[office]'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office][street]', 'children[address].data[office].street'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office][street]', 'children[address].data[office].street.prop'),
            array(self::LEVEL_2, 'address', 'address', 'street', '[office][street]', 'children[address].data[office][street]'),
            array(self::LEVEL_2, 'address', 'address', 'street', '[office][street]', 'children[address].data[office][street].prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office][street]', 'data.address.office.street'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office][street]', 'data.address.office.street.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office][street]', 'data.address.office[street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office][street]', 'data.address.office[street].prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office][street]', 'data.address[office].street'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[office][street]', 'data.address[office].street.prop'),
            array(self::LEVEL_2, 'address', 'address', 'street', '[office][street]', 'data.address[office][street]'),
            array(self::LEVEL_2, 'address', 'address', 'street', '[office][street]', 'data.address[office][street].prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office][street]', 'data[address].office.street'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office][street]', 'data[address].office.street.prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office][street]', 'data[address].office[street]'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office][street]', 'data[address].office[street].prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office][street]', 'data[address][office].street'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office][street]', 'data[address][office].street.prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office][street]', 'data[address][office][street]'),
            array(self::LEVEL_0, 'address', 'address', 'street', '[office][street]', 'data[address][office][street].prop'),

            array(self::LEVEL_2, 'address', '[address]', 'street', '[office][street]', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', '[address]', 'street', '[office][street]', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office][street]', 'children[address].data'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office][street]', 'children[address].data.office'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office][street]', 'children[address].data.office.street'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office][street]', 'children[address].data.office.street.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office][street]', 'children[address].data.office[street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office][street]', 'children[address].data.office[street].prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office][street]', 'children[address].data[office]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office][street]', 'children[address].data[office].street'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office][street]', 'children[address].data[office].street.prop'),
            array(self::LEVEL_2, 'address', '[address]', 'street', '[office][street]', 'children[address].data[office][street]'),
            array(self::LEVEL_2, 'address', '[address]', 'street', '[office][street]', 'children[address].data[office][street].prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office][street]', 'data.address.office.street'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office][street]', 'data.address.office.street.prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office][street]', 'data.address.office[street]'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office][street]', 'data.address.office[street].prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office][street]', 'data.address[office].street'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office][street]', 'data.address[office].street.prop'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office][street]', 'data.address[office][street]'),
            array(self::LEVEL_0, 'address', '[address]', 'street', '[office][street]', 'data.address[office][street].prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office][street]', 'data[address].office.street'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office][street]', 'data[address].office.street.prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office][street]', 'data[address].office[street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office][street]', 'data[address].office[street].prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office][street]', 'data[address][office].street'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[office][street]', 'data[address][office].street.prop'),
            array(self::LEVEL_2, 'address', '[address]', 'street', '[office][street]', 'data[address][office][street]'),
            array(self::LEVEL_2, 'address', '[address]', 'street', '[office][street]', 'data[address][office][street].prop'),

            // Edge cases which must not occur
            array(self::LEVEL_1, 'address', 'address', 'street', 'street', 'children[address][street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'street', 'children[address][street].prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[street]', 'children[address][street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', '[street]', 'children[address][street].prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'street', 'children[address][street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', 'street', 'children[address][street].prop'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[street]', 'children[address][street]'),
            array(self::LEVEL_1, 'address', '[address]', 'street', '[street]', 'children[address][street].prop'),

            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'children[person].children[address].children[street]'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'children[person].children[address].data.street'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'children[person].data.address.street'),
            array(self::LEVEL_0, 'address', 'person.address', 'street', 'street', 'data.address.street'),

            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'children[address].children[office].children[street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'children[address].children[office].data.street'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'children[address].data.street'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'office.street', 'data.address.street'),
        );
    }

    /**
     * @dataProvider provideDefaultTests
     */
    public function testDefaultErrorMapping($target, $childName, $childPath, $grandChildName, $grandChildPath, $violationPath)
    {
        $violation = $this->getConstraintViolation($violationPath);
        $parent = $this->getForm('parent');
        $child = $this->getForm($childName, $childPath);
        $grandChild = $this->getForm($grandChildName, $grandChildPath);

        $parent->add($child);
        $child->add($grandChild);

        $this->mapper->mapViolation($violation, $parent);

        if (self::LEVEL_0 === $target) {
            $this->assertEquals(array($this->getFormError()), $parent->getErrors(), $parent->getName() . ' should have an error, but has none');
            $this->assertCount(0, $child->getErrors(), $childName . ' should not have an error, but has one');
            $this->assertCount(0, $grandChild->getErrors(), $grandChildName . ' should not have an error, but has one');
        } elseif (self::LEVEL_1 === $target) {
            $this->assertCount(0, $parent->getErrors(), $parent->getName() . ' should not have an error, but has one');
            $this->assertEquals(array($this->getFormError()), $child->getErrors(), $childName . ' should have an error, but has none');
            $this->assertCount(0, $grandChild->getErrors(), $grandChildName . ' should not have an error, but has one');
        } else {
            $this->assertCount(0, $parent->getErrors(), $parent->getName() . ' should not have an error, but has one');
            $this->assertCount(0, $child->getErrors(), $childName . ' should not have an error, but has one');
            $this->assertEquals(array($this->getFormError()), $grandChild->getErrors(), $grandChildName. ' should have an error, but has none');
        }
    }

    public function provideCustomDataErrorTests()
    {
        return array(
            // mapping target, error mapping, child name, its property path, grand child name, its property path, violation path
            array(self::LEVEL_1, 'foo', 'address', 'address', 'address', 'street', 'street', 'data.foo'),
            array(self::LEVEL_1, 'foo', 'address', 'address', 'address', 'street', 'street', 'data.foo.prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', 'street', 'data[foo]'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', 'street', 'data[foo].prop'),

            array(self::LEVEL_1, 'foo', 'address', 'address', 'address', 'street', 'street', 'data.address'),
            array(self::LEVEL_1, 'foo', 'address', 'address', 'address', 'street', 'street', 'data.address.prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', 'street', 'data[address]'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', 'street', 'data[address].prop'),

            array(self::LEVEL_1, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.foo'),
            array(self::LEVEL_1, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.foo.prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[foo]'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[foo].prop'),

            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.address'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.address.prop'),
            array(self::LEVEL_1, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[address]'),
            array(self::LEVEL_1, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[address].prop'),

            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.foo'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.foo.prop'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[foo]'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[foo].prop'),

            array(self::LEVEL_1, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.address'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.address.prop'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[address]'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[address].prop'),

            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data.foo'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data.foo.prop'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data[foo]'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data[foo].prop'),

            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data.address'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data.address.prop'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data[address]'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data[address].prop'),

            array(self::LEVEL_2, 'foo', 'address', 'address', 'address', 'street', 'street', 'data.foo.street'),
            array(self::LEVEL_2, 'foo', 'address', 'address', 'address', 'street', 'street', 'data.foo.street.prop'),
            array(self::LEVEL_1, 'foo', 'address', 'address', 'address', 'street', 'street', 'data.foo[street]'),
            array(self::LEVEL_1, 'foo', 'address', 'address', 'address', 'street', 'street', 'data.foo[street].prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', 'street', 'data[foo].street'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', 'street', 'data[foo].street.prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', 'street', 'data[foo][street]'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', 'street', 'data[foo][street].prop'),

            array(self::LEVEL_2, 'foo', 'address', 'address', 'address', 'street', 'street', 'data.address.street'),
            array(self::LEVEL_2, 'foo', 'address', 'address', 'address', 'street', 'street', 'data.address.street.prop'),
            array(self::LEVEL_1, 'foo', 'address', 'address', 'address', 'street', 'street', 'data.address[street]'),
            array(self::LEVEL_1, 'foo', 'address', 'address', 'address', 'street', 'street', 'data.address[street].prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', 'street', 'data[address].street'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', 'street', 'data[address].street.prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', 'street', 'data[address][street]'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', 'street', 'data[address][street].prop'),

            array(self::LEVEL_1, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data.foo.street'),
            array(self::LEVEL_1, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data.foo.street.prop'),
            array(self::LEVEL_2, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data.foo[street]'),
            array(self::LEVEL_2, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data.foo[street].prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data[foo].street'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data[foo].street.prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data[foo][street]'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data[foo][street].prop'),

            array(self::LEVEL_1, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data.address.street'),
            array(self::LEVEL_1, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data.address.street.prop'),
            array(self::LEVEL_2, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data.address[street]'),
            array(self::LEVEL_2, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data.address[street].prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data[address].street'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data[address].street.prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data[address][street]'),
            array(self::LEVEL_0, 'foo', 'address', 'address', 'address', 'street', '[street]', 'data[address][street].prop'),

            array(self::LEVEL_2, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.foo.street'),
            array(self::LEVEL_2, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.foo.street.prop'),
            array(self::LEVEL_1, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.foo[street]'),
            array(self::LEVEL_1, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.foo[street].prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[foo].street'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[foo].street.prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[foo][street]'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[foo][street].prop'),

            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.address.street'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.address.street.prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.address[street]'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.address[street].prop'),
            array(self::LEVEL_2, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[address].street'),
            array(self::LEVEL_2, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[address].street.prop'),
            array(self::LEVEL_1, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[address][street]'),
            array(self::LEVEL_1, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[address][street].prop'),

            array(self::LEVEL_1, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data.foo.street'),
            array(self::LEVEL_1, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data.foo.street.prop'),
            array(self::LEVEL_2, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data.foo[street]'),
            array(self::LEVEL_2, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data.foo[street].prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data[foo].street'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data[foo].street.prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data[foo][street]'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data[foo][street].prop'),

            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data.address.street'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data.address.street.prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data.address[street]'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data.address[street].prop'),
            array(self::LEVEL_1, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data[address].street'),
            array(self::LEVEL_1, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data[address].street.prop'),
            array(self::LEVEL_2, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data[address][street]'),
            array(self::LEVEL_2, 'foo', 'address', 'address', '[address]', 'street', '[street]', 'data[address][street].prop'),

            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.foo.street'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.foo.street.prop'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.foo[street]'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.foo[street].prop'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[foo].street'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[foo].street.prop'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[foo][street]'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[foo][street].prop'),

            array(self::LEVEL_2, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.address.street'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.address.street.prop'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.address[street]'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.address[street].prop'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[address].street'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[address].street.prop'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[address][street]'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[address][street].prop'),

            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data.foo.street'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data.foo.street.prop'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data.foo[street]'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data.foo[street].prop'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data[foo].street'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data[foo].street.prop'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data[foo][street]'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data[foo][street].prop'),

            array(self::LEVEL_1, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data.address.street'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data.address.street.prop'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data.address[street]'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data.address[street].prop'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data[address].street'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data[address].street.prop'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data[address][street]'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', '[street]', 'data[address][street].prop'),

            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data.foo.street'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data.foo.street.prop'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data.foo[street]'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data.foo[street].prop'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data[foo].street'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data[foo].street.prop'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data[foo][street]'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data[foo][street].prop'),

            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data.address.street'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data.address.street.prop'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data.address[street]'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data.address[street].prop'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data[address].street'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data[address].street.prop'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data[address][street]'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', '[address]', 'street', 'street', 'data[address][street].prop'),

            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data.foo.street'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data.foo.street.prop'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data.foo[street]'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data.foo[street].prop'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data[foo].street'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data[foo].street.prop'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data[foo][street]'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data[foo][street].prop'),

            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data.address.street'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data.address.street.prop'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data.address[street]'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data.address[street].prop'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data[address].street'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data[address].street.prop'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data[address][street]'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', '[address]', 'street', '[street]', 'data[address][street].prop'),

            array(self::LEVEL_1, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar'),
            array(self::LEVEL_1, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar.prop'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar]'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar].prop'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar.prop'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar]'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar].prop'),

            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar.prop'),
            array(self::LEVEL_1, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar]'),
            array(self::LEVEL_1, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar].prop'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar.prop'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar]'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar].prop'),

            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar.prop'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar]'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar].prop'),
            array(self::LEVEL_1, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar'),
            array(self::LEVEL_1, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar.prop'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar]'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar].prop'),

            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar.prop'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar]'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar].prop'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar.prop'),
            array(self::LEVEL_1, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar]'),
            array(self::LEVEL_1, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar].prop'),

            array(self::LEVEL_2, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar.street'),
            array(self::LEVEL_2, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar.street.prop'),
            array(self::LEVEL_1, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar[street]'),
            array(self::LEVEL_1, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar[street].prop'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar].street'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar].street.prop'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar][street]'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar][street].prop'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar.street'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar.street.prop'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar[street]'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar[street].prop'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar].street'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar].street.prop'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar][street]'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar][street].prop'),

            array(self::LEVEL_1, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar.street'),
            array(self::LEVEL_1, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar.street.prop'),
            array(self::LEVEL_2, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar[street]'),
            array(self::LEVEL_2, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar[street].prop'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar].street'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar].street.prop'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar][street]'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar][street].prop'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar.street'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar.street.prop'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar[street]'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar[street].prop'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar].street'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar].street.prop'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar][street]'),
            array(self::LEVEL_0, 'foo.bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar][street].prop'),

            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar.street'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar.street.prop'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar[street]'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar[street].prop'),
            array(self::LEVEL_2, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar].street'),
            array(self::LEVEL_2, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar].street.prop'),
            array(self::LEVEL_1, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar][street]'),
            array(self::LEVEL_1, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar][street].prop'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar.street'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar.street.prop'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar[street]'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar[street].prop'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar].street'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar].street.prop'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar][street]'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar][street].prop'),

            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar.street'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar.street.prop'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar[street]'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar[street].prop'),
            array(self::LEVEL_1, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar].street'),
            array(self::LEVEL_1, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar].street.prop'),
            array(self::LEVEL_2, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar][street]'),
            array(self::LEVEL_2, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar][street].prop'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar.street'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar.street.prop'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar[street]'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar[street].prop'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar].street'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar].street.prop'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar][street]'),
            array(self::LEVEL_0, 'foo[bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar][street].prop'),

            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar.street'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar.street.prop'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar[street]'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar[street].prop'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar].street'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar].street.prop'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar][street]'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar][street].prop'),
            array(self::LEVEL_2, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar.street'),
            array(self::LEVEL_2, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar.street.prop'),
            array(self::LEVEL_1, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar[street]'),
            array(self::LEVEL_1, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar[street].prop'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar].street'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar].street.prop'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar][street]'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar][street].prop'),

            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar.street'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar.street.prop'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar[street]'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar[street].prop'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar].street'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar].street.prop'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar][street]'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar][street].prop'),
            array(self::LEVEL_1, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar.street'),
            array(self::LEVEL_1, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar.street.prop'),
            array(self::LEVEL_2, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar[street]'),
            array(self::LEVEL_2, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar[street].prop'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar].street'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar].street.prop'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar][street]'),
            array(self::LEVEL_0, '[foo].bar', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar][street].prop'),

            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar.street'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar.street.prop'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar[street]'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo.bar[street].prop'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar].street'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar].street.prop'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar][street]'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data.foo[bar][street].prop'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar.street'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar.street.prop'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar[street]'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo].bar[street].prop'),
            array(self::LEVEL_2, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar].street'),
            array(self::LEVEL_2, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar].street.prop'),
            array(self::LEVEL_1, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar][street]'),
            array(self::LEVEL_1, '[foo][bar]', 'address', 'address', 'address', 'street', 'street', 'data[foo][bar][street].prop'),

            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar.street'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar.street.prop'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar[street]'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo.bar[street].prop'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar].street'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar].street.prop'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar][street]'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data.foo[bar][street].prop'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar.street'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar.street.prop'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar[street]'),
            array(self::LEVEL_0, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo].bar[street].prop'),
            array(self::LEVEL_1, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar].street'),
            array(self::LEVEL_1, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar].street.prop'),
            array(self::LEVEL_2, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar][street]'),
            array(self::LEVEL_2, '[foo][bar]', 'address', 'address', 'address', 'street', '[street]', 'data[foo][bar][street].prop'),

            array(self::LEVEL_2, 'foo', 'address.street', 'address', 'address', 'street', 'street', 'data.foo'),
            array(self::LEVEL_2, 'foo', 'address.street', 'address', 'address', 'street', 'street', 'data.foo.prop'),
            array(self::LEVEL_2, '[foo]', 'address.street', 'address', 'address', 'street', 'street', 'data[foo]'),
            array(self::LEVEL_2, '[foo]', 'address.street', 'address', 'address', 'street', 'street', 'data[foo].prop'),

            array(self::LEVEL_2, 'foo', 'address.street', 'address', 'address', 'street', '[street]', 'data.foo'),
            array(self::LEVEL_2, 'foo', 'address.street', 'address', 'address', 'street', '[street]', 'data.foo.prop'),
            array(self::LEVEL_2, '[foo]', 'address.street', 'address', 'address', 'street', '[street]', 'data[foo]'),
            array(self::LEVEL_2, '[foo]', 'address.street', 'address', 'address', 'street', '[street]', 'data[foo].prop'),

            array(self::LEVEL_2, 'foo', 'address.street', 'address', '[address]', 'street', 'street', 'data.foo'),
            array(self::LEVEL_2, 'foo', 'address.street', 'address', '[address]', 'street', 'street', 'data.foo.prop'),
            array(self::LEVEL_2, '[foo]', 'address.street', 'address', '[address]', 'street', 'street', 'data[foo]'),
            array(self::LEVEL_2, '[foo]', 'address.street', 'address', '[address]', 'street', 'street', 'data[foo].prop'),

            array(self::LEVEL_2, 'foo.bar', 'address.street', 'address', 'address', 'street', 'street', 'data.foo.bar'),
            array(self::LEVEL_2, 'foo.bar', 'address.street', 'address', 'address', 'street', 'street', 'data.foo.bar.prop'),
            array(self::LEVEL_2, 'foo[bar]', 'address.street', 'address', 'address', 'street', 'street', 'data.foo[bar]'),
            array(self::LEVEL_2, 'foo[bar]', 'address.street', 'address', 'address', 'street', 'street', 'data.foo[bar].prop'),
            array(self::LEVEL_2, '[foo].bar', 'address.street', 'address', 'address', 'street', 'street', 'data[foo].bar'),
            array(self::LEVEL_2, '[foo].bar', 'address.street', 'address', 'address', 'street', 'street', 'data[foo].bar.prop'),
            array(self::LEVEL_2, '[foo][bar]', 'address.street', 'address', 'address', 'street', 'street', 'data[foo][bar]'),
            array(self::LEVEL_2, '[foo][bar]', 'address.street', 'address', 'address', 'street', 'street', 'data[foo][bar].prop'),

            array(self::LEVEL_2, 'foo.bar', 'address.street', 'address', 'address', 'street', '[street]', 'data.foo.bar'),
            array(self::LEVEL_2, 'foo.bar', 'address.street', 'address', 'address', 'street', '[street]', 'data.foo.bar.prop'),
            array(self::LEVEL_2, 'foo[bar]', 'address.street', 'address', 'address', 'street', '[street]', 'data.foo[bar]'),
            array(self::LEVEL_2, 'foo[bar]', 'address.street', 'address', 'address', 'street', '[street]', 'data.foo[bar].prop'),
            array(self::LEVEL_2, '[foo].bar', 'address.street', 'address', 'address', 'street', '[street]', 'data[foo].bar'),
            array(self::LEVEL_2, '[foo].bar', 'address.street', 'address', 'address', 'street', '[street]', 'data[foo].bar.prop'),
            array(self::LEVEL_2, '[foo][bar]', 'address.street', 'address', 'address', 'street', '[street]', 'data[foo][bar]'),
            array(self::LEVEL_2, '[foo][bar]', 'address.street', 'address', 'address', 'street', '[street]', 'data[foo][bar].prop'),

            array(self::LEVEL_2, 'foo.bar', 'address.street', 'address', '[address]', 'street', 'street', 'data.foo.bar'),
            array(self::LEVEL_2, 'foo.bar', 'address.street', 'address', '[address]', 'street', 'street', 'data.foo.bar.prop'),
            array(self::LEVEL_2, 'foo[bar]', 'address.street', 'address', '[address]', 'street', 'street', 'data.foo[bar]'),
            array(self::LEVEL_2, 'foo[bar]', 'address.street', 'address', '[address]', 'street', 'street', 'data.foo[bar].prop'),
            array(self::LEVEL_2, '[foo].bar', 'address.street', 'address', '[address]', 'street', 'street', 'data[foo].bar'),
            array(self::LEVEL_2, '[foo].bar', 'address.street', 'address', '[address]', 'street', 'street', 'data[foo].bar.prop'),
            array(self::LEVEL_2, '[foo][bar]', 'address.street', 'address', '[address]', 'street', 'street', 'data[foo][bar]'),
            array(self::LEVEL_2, '[foo][bar]', 'address.street', 'address', '[address]', 'street', 'street', 'data[foo][bar].prop'),

            // Edge cases
            array(self::LEVEL_2, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.foo.street'),
            array(self::LEVEL_2, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.foo.street.prop'),
            array(self::LEVEL_1, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.foo[street]'),
            array(self::LEVEL_1, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data.foo[street].prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[foo].street'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[foo].street.prop'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[foo][street]'),
            array(self::LEVEL_0, 'foo', 'address', 'address', '[address]', 'street', 'street', 'data[foo][street].prop'),

            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.foo.street'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.foo.street.prop'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.foo[street]'),
            array(self::LEVEL_0, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data.foo[street].prop'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[foo].street'),
            array(self::LEVEL_2, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[foo].street.prop'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[foo][street]'),
            array(self::LEVEL_1, '[foo]', 'address', 'address', 'address', 'street', 'street', 'data[foo][street].prop'),
        );
    }

    /**
     * @dataProvider provideCustomDataErrorTests
     */
    public function testCustomDataErrorMapping($target, $mapFrom, $mapTo, $childName, $childPath, $grandChildName, $grandChildPath, $violationPath)
    {
        $violation = $this->getConstraintViolation($violationPath);
        $parent = $this->getForm('parent', null, null, array($mapFrom => $mapTo));
        $child = $this->getForm($childName, $childPath);
        $grandChild = $this->getForm($grandChildName, $grandChildPath);

        $parent->add($child);
        $child->add($grandChild);

        // Add a field mapped to the first element of $mapFrom
        // to try to distract the algorithm
        // Only add it if we expect the error to come up on a different
        // level than LEVEL_0, because in this case the error would
        // (correctly) be mapped to the distraction field
        if ($target !== self::LEVEL_0) {
            $mapFromPath = new Symfony_Component_PropertyAccess_PropertyPath($mapFrom);
            $mapFromPrefix = $mapFromPath->isIndex(0)
                ? '['.$mapFromPath->getElement(0).']'
                : $mapFromPath->getElement(0);
            $distraction = $this->getForm('distraction', $mapFromPrefix);

            $parent->add($distraction);
        }

        $this->mapper->mapViolation($violation, $parent);

        if ($target !== self::LEVEL_0) {
            $this->assertCount(0, $distraction->getErrors(), 'distraction should not have an error, but has one');
        }

        if (self::LEVEL_0 === $target) {
            $this->assertEquals(array($this->getFormError()), $parent->getErrors(), $parent->getName() . ' should have an error, but has none');
            $this->assertCount(0, $child->getErrors(), $childName . ' should not have an error, but has one');
            $this->assertCount(0, $grandChild->getErrors(), $grandChildName . ' should not have an error, but has one');
        } elseif (self::LEVEL_1 === $target) {
            $this->assertCount(0, $parent->getErrors(), $parent->getName() . ' should not have an error, but has one');
            $this->assertEquals(array($this->getFormError()), $child->getErrors(), $childName . ' should have an error, but has none');
            $this->assertCount(0, $grandChild->getErrors(), $grandChildName . ' should not have an error, but has one');
        } else {
            $this->assertCount(0, $parent->getErrors(), $parent->getName() . ' should not have an error, but has one');
            $this->assertCount(0, $child->getErrors(), $childName . ' should not have an error, but has one');
            $this->assertEquals(array($this->getFormError()), $grandChild->getErrors(), $grandChildName. ' should have an error, but has none');
        }
    }

    public function provideCustomFormErrorTests()
    {
        // This case is different than the data errors, because here the
        // left side of the mapping refers to the property path of the actual
        // children. In other words, a child error only works if
        // 1) the error actually maps to an existing child and
        // 2) the property path of that child (relative to the form providing
        //    the mapping) matches the left side of the mapping
        return array(
            // mapping target, map from, map to, child name, its property path, grand child name, its property path, violation path
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[foo].children[street].data'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[foo].children[street].data.prop'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[foo].data.street'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[foo].data.street.prop'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[foo].data[street]'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[foo].data[street].prop'),

            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].children[street].data.prop'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].data.street.prop'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].data[street].prop'),

            // Property path of the erroneous field and mapping must match exactly
            array(self::LEVEL_1B, 'foo', 'address', 'foo', '[foo]', 'address', 'address', 'street', 'street', 'children[foo].children[street].data'),
            array(self::LEVEL_1B, 'foo', 'address', 'foo', '[foo]', 'address', 'address', 'street', 'street', 'children[foo].children[street].data.prop'),
            array(self::LEVEL_1B, 'foo', 'address', 'foo', '[foo]', 'address', 'address', 'street', 'street', 'children[foo].data.street'),
            array(self::LEVEL_1B, 'foo', 'address', 'foo', '[foo]', 'address', 'address', 'street', 'street', 'children[foo].data.street.prop'),
            array(self::LEVEL_1B, 'foo', 'address', 'foo', '[foo]', 'address', 'address', 'street', 'street', 'children[foo].data[street]'),
            array(self::LEVEL_1B, 'foo', 'address', 'foo', '[foo]', 'address', 'address', 'street', 'street', 'children[foo].data[street].prop'),

            array(self::LEVEL_1B, '[foo]', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[foo].children[street].data'),
            array(self::LEVEL_1B, '[foo]', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[foo].children[street].data.prop'),
            array(self::LEVEL_1B, '[foo]', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[foo].data.street'),
            array(self::LEVEL_1B, '[foo]', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[foo].data.street.prop'),
            array(self::LEVEL_1B, '[foo]', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[foo].data[street]'),
            array(self::LEVEL_1B, '[foo]', 'address', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[foo].data[street].prop'),

            array(self::LEVEL_1, '[foo]', 'address', 'foo', '[foo]', 'address', 'address', 'street', 'street', 'children[foo].children[street].data'),
            array(self::LEVEL_1, '[foo]', 'address', 'foo', '[foo]', 'address', 'address', 'street', 'street', 'children[foo].children[street].data.prop'),
            array(self::LEVEL_2, '[foo]', 'address', 'foo', '[foo]', 'address', 'address', 'street', 'street', 'children[foo].data.street'),
            array(self::LEVEL_2, '[foo]', 'address', 'foo', '[foo]', 'address', 'address', 'street', 'street', 'children[foo].data.street.prop'),
            array(self::LEVEL_1, '[foo]', 'address', 'foo', '[foo]', 'address', 'address', 'street', 'street', 'children[foo].data[street]'),
            array(self::LEVEL_1, '[foo]', 'address', 'foo', '[foo]', 'address', 'address', 'street', 'street', 'children[foo].data[street].prop'),

            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[foo].children[street].data'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[foo].children[street].data.prop'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[foo].data.street'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[foo].data.street.prop'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[foo].data[street]'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[foo].data[street].prop'),

            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].data.street.prop'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].data[street]'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].data[street].prop'),

            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[foo].children[street].data'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[foo].children[street].data.prop'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[foo].data.street'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[foo].data.street.prop'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[foo].data[street]'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[foo].data[street].prop'),

            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].children[street].data.prop'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].data.street.prop'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].data[street].prop'),

            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[foo].children[street].data'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[foo].children[street].data.prop'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[foo].data.street'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[foo].data.street.prop'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[foo].data[street]'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[foo].data[street].prop'),

            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].children[street].data.prop'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_1, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].data.street.prop'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].data[street]'),
            array(self::LEVEL_2, 'foo', 'address', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].data[street].prop'),

            // Map to a nested child
            array(self::LEVEL_2, 'foo', 'address.street', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[foo]'),
            array(self::LEVEL_2, 'foo', 'address.street', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[foo]'),
            array(self::LEVEL_2, 'foo', 'address.street', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[foo]'),
            array(self::LEVEL_2, 'foo', 'address.street', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[foo]'),

            // Map from a nested child
            array(self::LEVEL_1B, 'address.street', 'foo', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].children[street]'),
            array(self::LEVEL_1B, 'address.street', 'foo', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_1, 'address.street', 'foo', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_2, 'address.street', 'foo', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].children[street]'),
            array(self::LEVEL_1B, 'address.street', 'foo', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_2, 'address.street', 'foo', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].data[street]'),
            array(self::LEVEL_2, 'address.street', 'foo', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].children[street]'),
            array(self::LEVEL_2, 'address.street', 'foo', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_1, 'address.street', 'foo', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_2, 'address.street', 'foo', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].children[street]'),
            array(self::LEVEL_1, 'address.street', 'foo', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_2, 'address.street', 'foo', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].data[street]'),

            array(self::LEVEL_2, 'address[street]', 'foo', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].children[street]'),
            array(self::LEVEL_2, 'address[street]', 'foo', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_1B, 'address[street]', 'foo', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_1B, 'address[street]', 'foo', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].children[street]'),
            array(self::LEVEL_1, 'address[street]', 'foo', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_1B, 'address[street]', 'foo', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].data[street]'),
            array(self::LEVEL_2, 'address[street]', 'foo', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].children[street]'),
            array(self::LEVEL_2, 'address[street]', 'foo', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_1, 'address[street]', 'foo', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_2, 'address[street]', 'foo', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].children[street]'),
            array(self::LEVEL_1, 'address[street]', 'foo', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_2, 'address[street]', 'foo', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].data[street]'),

            array(self::LEVEL_2, '[address].street', 'foo', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].children[street]'),
            array(self::LEVEL_2, '[address].street', 'foo', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_1, '[address].street', 'foo', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_2, '[address].street', 'foo', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].children[street]'),
            array(self::LEVEL_1, '[address].street', 'foo', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_2, '[address].street', 'foo', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].data[street]'),
            array(self::LEVEL_1B, '[address].street', 'foo', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].children[street]'),
            array(self::LEVEL_1B, '[address].street', 'foo', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_1, '[address].street', 'foo', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_2, '[address].street', 'foo', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].children[street]'),
            array(self::LEVEL_1B, '[address].street', 'foo', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_2, '[address].street', 'foo', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].data[street]'),

            array(self::LEVEL_2, '[address][street]', 'foo', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].children[street]'),
            array(self::LEVEL_2, '[address][street]', 'foo', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_1, '[address][street]', 'foo', 'foo', 'foo', 'address', 'address', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_2, '[address][street]', 'foo', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].children[street]'),
            array(self::LEVEL_1, '[address][street]', 'foo', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_2, '[address][street]', 'foo', 'foo', 'foo', 'address', 'address', 'street', '[street]', 'children[address].data[street]'),
            array(self::LEVEL_2, '[address][street]', 'foo', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].children[street]'),
            array(self::LEVEL_2, '[address][street]', 'foo', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_1B, '[address][street]', 'foo', 'foo', 'foo', 'address', '[address]', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_1B, '[address][street]', 'foo', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].children[street]'),
            array(self::LEVEL_1, '[address][street]', 'foo', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].data.street'),
            array(self::LEVEL_1B, '[address][street]', 'foo', 'foo', 'foo', 'address', '[address]', 'street', '[street]', 'children[address].data[street]'),
        );
    }

    /**
     * @dataProvider provideCustomFormErrorTests
     */
    public function testCustomFormErrorMapping($target, $mapFrom, $mapTo, $errorName, $errorPath, $childName, $childPath, $grandChildName, $grandChildPath, $violationPath)
    {
        $violation = $this->getConstraintViolation($violationPath);
        $parent = $this->getForm('parent', null, null, array($mapFrom => $mapTo));
        $child = $this->getForm($childName, $childPath);
        $grandChild = $this->getForm($grandChildName, $grandChildPath);
        $errorChild = $this->getForm($errorName, $errorPath);

        $parent->add($child);
        $parent->add($errorChild);
        $child->add($grandChild);

        $this->mapper->mapViolation($violation, $parent);

        if (self::LEVEL_0 === $target) {
            $this->assertCount(0, $errorChild->getErrors(), $errorName . ' should not have an error, but has one');
            $this->assertEquals(array($this->getFormError()), $parent->getErrors(), $parent->getName() . ' should have an error, but has none');
            $this->assertCount(0, $child->getErrors(), $childName . ' should not have an error, but has one');
            $this->assertCount(0, $grandChild->getErrors(), $grandChildName . ' should not have an error, but has one');
        } elseif (self::LEVEL_1 === $target) {
            $this->assertCount(0, $errorChild->getErrors(), $errorName . ' should not have an error, but has one');
            $this->assertCount(0, $parent->getErrors(), $parent->getName() . ' should not have an error, but has one');
            $this->assertEquals(array($this->getFormError()), $child->getErrors(), $childName . ' should have an error, but has none');
            $this->assertCount(0, $grandChild->getErrors(), $grandChildName . ' should not have an error, but has one');
        } elseif (self::LEVEL_1B === $target) {
            $this->assertEquals(array($this->getFormError()), $errorChild->getErrors(), $errorName . ' should have an error, but has none');
            $this->assertCount(0, $parent->getErrors(), $parent->getName() . ' should not have an error, but has one');
            $this->assertCount(0, $child->getErrors(), $childName . ' should not have an error, but has one');
            $this->assertCount(0, $grandChild->getErrors(), $grandChildName . ' should not have an error, but has one');
        } else {
            $this->assertCount(0, $errorChild->getErrors(), $errorName . ' should not have an error, but has one');
            $this->assertCount(0, $parent->getErrors(), $parent->getName() . ' should not have an error, but has one');
            $this->assertCount(0, $child->getErrors(), $childName . ' should not have an error, but has one');
            $this->assertEquals(array($this->getFormError()), $grandChild->getErrors(), $grandChildName. ' should have an error, but has none');
        }
    }

    public function provideVirtualFormErrorTests()
    {
        return array(
            // mapping target, child name, its property path, grand child name, its property path, violation path
            array(self::LEVEL_2, 'address', 'address', 'street', 'street', 'children[address].children[street].data'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'street', 'children[address].children[street].data.prop'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'street', 'children[address].data.street'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'street', 'children[address].data.street.prop'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'street', 'children[address].data[street]'),
            array(self::LEVEL_1, 'address', 'address', 'street', 'street', 'children[address].data[street].prop'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'street', 'data.street'),
            array(self::LEVEL_2, 'address', 'address', 'street', 'street', 'data.street.prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', 'data[street]'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', 'data[street].prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', 'data.address.street'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', 'data.address.street.prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', 'data.address[street]'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', 'data.address[street].prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', 'data[address].street'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', 'data[address].street.prop'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', 'data[address][street]'),
            array(self::LEVEL_0, 'address', 'address', 'street', 'street', 'data[address][street].prop'),
        );
    }

    /**
     * @dataProvider provideVirtualFormErrorTests
     */
    public function testVirtualFormErrorMapping($target, $childName, $childPath, $grandChildName, $grandChildPath, $violationPath)
    {
        $violation = $this->getConstraintViolation($violationPath);
        $parent = $this->getForm('parent');
        $child = $this->getForm($childName, $childPath, null, array(), true);
        $grandChild = $this->getForm($grandChildName, $grandChildPath);

        $parent->add($child);
        $child->add($grandChild);

        $this->mapper->mapViolation($violation, $parent);

        if (self::LEVEL_0 === $target) {
            $this->assertEquals(array($this->getFormError()), $parent->getErrors(), $parent->getName() . ' should have an error, but has none');
            $this->assertCount(0, $child->getErrors(), $childName . ' should not have an error, but has one');
            $this->assertCount(0, $grandChild->getErrors(), $grandChildName . ' should not have an error, but has one');
        } elseif (self::LEVEL_1 === $target) {
            $this->assertCount(0, $parent->getErrors(), $parent->getName() . ' should not have an error, but has one');
            $this->assertEquals(array($this->getFormError()), $child->getErrors(), $childName . ' should have an error, but has none');
            $this->assertCount(0, $grandChild->getErrors(), $grandChildName . ' should not have an error, but has one');
        } else {
            $this->assertCount(0, $parent->getErrors(), $parent->getName() . ' should not have an error, but has one');
            $this->assertCount(0, $child->getErrors(), $childName . ' should not have an error, but has one');
            $this->assertEquals(array($this->getFormError()), $grandChild->getErrors(), $grandChildName. ' should have an error, but has none');
        }
    }
}
