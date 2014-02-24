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
class Symfony_Component_Form_Tests_FormRegistryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony_Component_Form_FormRegistry
     */
    private $registry;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $resolvedTypeFactory;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $guesser1;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $guesser2;

    /**
     * @var Symfony_Component_Form_Tests_Fixtures_TestExtension
     */
    private $extension1;

    /**
     * @var Symfony_Component_Form_Tests_Fixtures_TestExtension
     */
    private $extension2;

    protected function setUp()
    {
        $this->resolvedTypeFactory = $this->getMock('Symfony_Component_Form_ResolvedFormTypeFactory');
        $this->guesser1 = $this->getMock('Symfony_Component_Form_FormTypeGuesserInterface');
        $this->guesser2 = $this->getMock('Symfony_Component_Form_FormTypeGuesserInterface');
        $this->extension1 = new Symfony_Component_Form_Tests_Fixtures_TestExtension($this->guesser1);
        $this->extension2 = new Symfony_Component_Form_Tests_Fixtures_TestExtension($this->guesser2);
        $this->registry = new Symfony_Component_Form_FormRegistry(array(
            $this->extension1,
            $this->extension2,
        ), $this->resolvedTypeFactory);
    }

    public function testGetTypeReturnsAddedType()
    {
        $resolvedType = $this->getMock('Symfony_Component_Form_ResolvedFormTypeInterface');

        $resolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));

        set_error_handler(array('Symfony_Component_Form_Test_DeprecationErrorHandler', 'handle'));
        $this->registry->addType($resolvedType);
        restore_error_handler();

        $this->assertSame($resolvedType, $this->registry->getType('foo'));
    }

    public function testGetTypeFromExtension()
    {
        $type = new Symfony_Component_Form_Tests_Fixtures_FooType();
        $resolvedType = $this->getMock('Symfony_Component_Form_ResolvedFormTypeInterface');

        $this->extension2->addType($type);

        $this->resolvedTypeFactory->expects($this->once())
            ->method('createResolvedType')
            ->with($type)
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $resolvedType = $this->registry->getType('foo');

        $this->assertSame($resolvedType, $this->registry->getType('foo'));
    }

    public function testGetTypeWithTypeExtensions()
    {
        $type = new Symfony_Component_Form_Tests_Fixtures_FooType();
        $ext1 = new Symfony_Component_Form_Tests_Fixtures_FooTypeBarExtension();
        $ext2 = new Symfony_Component_Form_Tests_Fixtures_FooTypeBazExtension();
        $resolvedType = $this->getMock('Symfony_Component_Form_ResolvedFormTypeInterface');

        $this->extension2->addType($type);
        $this->extension1->addTypeExtension($ext1);
        $this->extension2->addTypeExtension($ext2);

        $this->resolvedTypeFactory->expects($this->once())
            ->method('createResolvedType')
            ->with($type, array($ext1, $ext2))
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $this->assertSame($resolvedType, $this->registry->getType('foo'));
    }

    public function testGetTypeConnectsParent()
    {
        $parentType = new Symfony_Component_Form_Tests_Fixtures_FooType();
        $type = new Symfony_Component_Form_Tests_Fixtures_FooSubType();
        $parentResolvedType = $this->getMock('Symfony_Component_Form_ResolvedFormTypeInterface');
        $resolvedType = $this->getMock('Symfony_Component_Form_ResolvedFormTypeInterface');

        $this->extension1->addType($parentType);
        $this->extension2->addType($type);

        $this->resolvedTypeFactory->expects($this->at(0))
            ->method('createResolvedType')
            ->with($parentType)
            ->will($this->returnValue($parentResolvedType));

        $this->resolvedTypeFactory->expects($this->at(1))
            ->method('createResolvedType')
            ->with($type, array(), $parentResolvedType)
            ->will($this->returnValue($resolvedType));

        $parentResolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $resolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo_sub_type'));

        $this->assertSame($resolvedType, $this->registry->getType('foo_sub_type'));
    }

    public function testGetTypeConnectsParentIfGetParentReturnsInstance()
    {
        $type = new Symfony_Component_Form_Tests_Fixtures_FooSubTypeWithParentInstance();
        $parentResolvedType = $this->getMock('Symfony_Component_Form_ResolvedFormTypeInterface');
        $resolvedType = $this->getMock('Symfony_Component_Form_ResolvedFormTypeInterface');

        $this->extension1->addType($type);

        $this->resolvedTypeFactory->expects($this->at(0))
            ->method('createResolvedType')
            ->with($this->isInstanceOf('Symfony_Component_Form_Tests_Fixtures_FooType'))
            ->will($this->returnValue($parentResolvedType));

        $this->resolvedTypeFactory->expects($this->at(1))
            ->method('createResolvedType')
            ->with($type, array(), $parentResolvedType)
            ->will($this->returnValue($resolvedType));

        $parentResolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $resolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo_sub_type_parent_instance'));

        $this->assertSame($resolvedType, $this->registry->getType('foo_sub_type_parent_instance'));
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_Exception
     */
    public function testGetTypeThrowsExceptionIfParentNotFound()
    {
        $type = new Symfony_Component_Form_Tests_Fixtures_FooSubType();

        $this->extension1->addType($type);

        $this->registry->getType($type);
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_Exception
     */
    public function testGetTypeThrowsExceptionIfTypeNotFound()
    {
        $this->registry->getType('bar');
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testGetTypeThrowsExceptionIfNoString()
    {
        $this->registry->getType(array());
    }

    public function testHasTypeAfterAdding()
    {
        $resolvedType = $this->getMock('Symfony_Component_Form_ResolvedFormTypeInterface');

        $resolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $this->assertFalse($this->registry->hasType('foo'));

        set_error_handler(array('Symfony_Component_Form_Test_DeprecationErrorHandler', 'handle'));
        $this->registry->addType($resolvedType);
        restore_error_handler();

        $this->assertTrue($this->registry->hasType('foo'));
    }

    public function testHasTypeAfterLoadingFromExtension()
    {
        $type = new Symfony_Component_Form_Tests_Fixtures_FooType();
        $resolvedType = $this->getMock('Symfony_Component_Form_ResolvedFormTypeInterface');

        $this->resolvedTypeFactory->expects($this->once())
            ->method('createResolvedType')
            ->with($type)
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $this->assertFalse($this->registry->hasType('foo'));

        $this->extension2->addType($type);

        $this->assertTrue($this->registry->hasType('foo'));
    }

    public function testGetTypeGuesser()
    {
        $expectedGuesser = new Symfony_Component_Form_FormTypeGuesserChain(array($this->guesser1, $this->guesser2));

        $this->assertEquals($expectedGuesser, $this->registry->getTypeGuesser());

        $registry = new Symfony_Component_Form_FormRegistry(
            array($this->getMock('Symfony_Component_Form_FormExtensionInterface')),
            $this->resolvedTypeFactory);

        $this->assertNull($registry->getTypeGuesser());
    }

    public function testGetExtensions()
    {
        $expectedExtensions = array($this->extension1, $this->extension2);

        $this->assertEquals($expectedExtensions, $this->registry->getExtensions());
    }
}
