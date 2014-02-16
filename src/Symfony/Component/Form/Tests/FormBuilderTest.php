<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_FormBuilderTest extends PHPUnit_Framework_TestCase
{
    private $dispatcher;

    private $factory;

    private $builder;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        $this->dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $this->factory = $this->getMock('Symfony_Component_Form_FormFactoryInterface');
        $this->builder = new Symfony_Component_Form_FormBuilder('name', null, $this->dispatcher, $this->factory);
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->factory = null;
        $this->builder = null;
    }

    /**
     * Changing the name is not allowed, otherwise the name and property path
     * are not synchronized anymore
     *
     * @see FormType::buildForm
     */
    public function testNoSetName()
    {
        $this->assertFalse(method_exists($this->builder, 'setName'));
    }

    public function testAddNameNoStringAndNoInteger()
    {
        $this->setExpectedException('Symfony_Component_Form_Exception_UnexpectedTypeException');
        $this->builder->add(true);
    }

    public function testAddTypeNoString()
    {
        $this->setExpectedException('Symfony_Component_Form_Exception_UnexpectedTypeException');
        $this->builder->add('foo', 1234);
    }

    public function testAddWithGuessFluent()
    {
        $this->builder = new Symfony_Component_Form_FormBuilder('name', 'stdClass', $this->dispatcher, $this->factory);
        $builder = $this->builder->add('foo');
        $this->assertSame($builder, $this->builder);
    }

    public function testAddIsFluent()
    {
        $builder = $this->builder->add('foo', 'text', array('bar' => 'baz'));
        $this->assertSame($builder, $this->builder);
    }

    public function testAdd()
    {
        $this->assertFalse($this->builder->has('foo'));
        $this->builder->add('foo', 'text');
        $this->assertTrue($this->builder->has('foo'));
    }

    public function testAddIntegerName()
    {
        $this->assertFalse($this->builder->has(0));
        $this->builder->add(0, 'text');
        $this->assertTrue($this->builder->has(0));
    }

    public function testAll()
    {
        $this->factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('foo', 'text')
            ->will($this->returnValue(new Symfony_Component_Form_FormBuilder('foo', null, $this->dispatcher, $this->factory)));

        $this->assertCount(0, $this->builder->all());
        $this->assertFalse($this->builder->has('foo'));

        $this->builder->add('foo', 'text');
        $children = $this->builder->all();

        $this->assertTrue($this->builder->has('foo'));
        $this->assertCount(1, $children);
        $this->assertArrayHasKey('foo', $children);
    }

    /*
     * https://github.com/symfony/symfony/issues/4693
     */
    public function testMaintainOrderOfLazyAndExplicitChildren()
    {
        $this->builder->add('foo', 'text');
        $this->builder->add($this->getFormBuilder('bar'));
        $this->builder->add('baz', 'text');

        $children = $this->builder->all();

        $this->assertSame(array('foo', 'bar', 'baz'), array_keys($children));
    }

    public function testAddFormType()
    {
        $this->assertFalse($this->builder->has('foo'));
        $this->builder->add('foo', $this->getMock('Symfony_Component_Form_FormTypeInterface'));
        $this->assertTrue($this->builder->has('foo'));
    }

    public function testRemove()
    {
        $this->builder->add('foo', 'text');
        $this->builder->remove('foo');
        $this->assertFalse($this->builder->has('foo'));
    }

    public function testRemoveUnknown()
    {
        $this->builder->remove('foo');
        $this->assertFalse($this->builder->has('foo'));
    }

    // https://github.com/symfony/symfony/pull/4826
    public function testRemoveAndGetForm()
    {
        $this->builder->add('foo', 'text');
        $this->builder->remove('foo');
        $form = $this->builder->getForm();
        $this->assertInstanceOf('Symfony_Component_Form_Form', $form);
    }

    public function testCreateNoTypeNo()
    {
        $this->factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('foo', 'text', null, array())
        ;

        $this->builder->create('foo');
    }

    public function testGetUnknown()
    {
        $this->setExpectedException('Symfony_Component_Form_Exception_Exception', 'The child with the name "foo" does not exist.');
        $this->builder->get('foo');
    }

    public function testGetExplicitType()
    {
        $expectedType = 'text';
        $expectedName = 'foo';
        $expectedOptions = array('bar' => 'baz');

        $this->factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with($expectedName, $expectedType, null, $expectedOptions)
            ->will($this->returnValue($this->getFormBuilder()));

        $this->builder->add($expectedName, $expectedType, $expectedOptions);
        $builder = $this->builder->get($expectedName);

        $this->assertNotSame($builder, $this->builder);
    }

    public function testGetGuessedType()
    {
        $expectedName = 'foo';
        $expectedOptions = array('bar' => 'baz');

        $this->factory->expects($this->once())
            ->method('createBuilderForProperty')
            ->with('stdClass', $expectedName, null, $expectedOptions)
            ->will($this->returnValue($this->getFormBuilder()));

        $this->builder = new Symfony_Component_Form_FormBuilder('name', 'stdClass', $this->dispatcher, $this->factory);
        $this->builder->add($expectedName, null, $expectedOptions);
        $builder = $this->builder->get($expectedName);

        $this->assertNotSame($builder, $this->builder);
    }

    public function testGetParent()
    {
        $this->assertNull($this->builder->getParent());
    }

    public function testGetParentForAddedBuilder()
    {
        $builder = new Symfony_Component_Form_FormBuilder('name', null, $this->dispatcher, $this->factory);
        $this->builder->add($builder);
        $this->assertSame($this->builder, $builder->getParent());
    }

    public function testGetParentForRemovedBuilder()
    {
        $builder = new Symfony_Component_Form_FormBuilder('name', null, $this->dispatcher, $this->factory);
        $this->builder->add($builder);
        $this->builder->remove('name');
        $this->assertNull($builder->getParent());
    }

    public function testGetParentForCreatedBuilder()
    {
        $this->builder = new Symfony_Component_Form_FormBuilder('name', 'stdClass', $this->dispatcher, $this->factory);
        $this->factory
            ->expects($this->once())
            ->method('createNamedBuilder')
            ->with('bar', 'text', null, array(), $this->builder)
        ;

        $this->factory
            ->expects($this->once())
            ->method('createBuilderForProperty')
            ->with('stdClass', 'foo', null, array(), $this->builder)
        ;

        $this->builder->create('foo');
        $this->builder->create('bar', 'text');
    }

    public function testGetFormConfigErasesReferences()
    {
        $builder = new Symfony_Component_Form_FormBuilder('name', null, $this->dispatcher, $this->factory);
        $builder->setParent(new Symfony_Component_Form_FormBuilder('parent', null, $this->dispatcher, $this->factory));
        $builder->add(new Symfony_Component_Form_FormBuilder('child', null, $this->dispatcher, $this->factory));

        $config = $builder->getFormConfig();

        $this->assertNull($this->readAttribute($config, 'parent'));
        $this->assertEmpty($this->readAttribute($config, 'children'));
        $this->assertEmpty($this->readAttribute($config, 'unresolvedChildren'));
    }

    private function getFormBuilder($name = 'name')
    {
        $mock = $this->getMockBuilder('Symfony_Component_Form_FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        return $mock;
    }
}
