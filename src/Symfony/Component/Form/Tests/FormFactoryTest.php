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
class Symfony_Component_Form_Tests_FormFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $guesser1;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $guesser2;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $resolvedTypeFactory;

    /**
     * @var Symfony_Component_Form_FormFactory
     */
    private $factory;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        $this->resolvedTypeFactory = $this->getMock('Symfony_Component_Form_ResolvedFormTypeFactoryInterface');
        $this->guesser1 = $this->getMock('Symfony_Component_Form_FormTypeGuesserInterface');
        $this->guesser2 = $this->getMock('Symfony_Component_Form_FormTypeGuesserInterface');
        $this->registry = $this->getMock('Symfony_Component_Form_FormRegistryInterface');
        $this->factory = new Symfony_Component_Form_FormFactory($this->registry, $this->resolvedTypeFactory);

        $this->registry->expects($this->any())
            ->method('getTypeGuesser')
            ->will($this->returnValue(new Symfony_Component_Form_FormTypeGuesserChain(array(
                $this->guesser1,
                $this->guesser2,
            ))));
    }

    public function testAddType()
    {
        $type = new Symfony_Component_Form_Tests_Fixtures_FooType();
        $resolvedType = $this->getMockResolvedType();

        $this->resolvedTypeFactory->expects($this->once())
            ->method('createResolvedType')
            ->with($type)
            ->will($this->returnValue($resolvedType));

        $this->registry->expects($this->once())
            ->method('addType')
            ->with($resolvedType);

        set_error_handler(array('Symfony_Component_Form_Test_DeprecationErrorHandler', 'handle'));
        $this->factory->addType($type);
        restore_error_handler();
    }

    public function testHasType()
    {
        $this->registry->expects($this->once())
            ->method('hasType')
            ->with('name')
            ->will($this->returnValue('RESULT'));

        set_error_handler(array('Symfony_Component_Form_Test_DeprecationErrorHandler', 'handle'));
        $this->assertSame('RESULT', $this->factory->hasType('name'));
        restore_error_handler();
    }

    public function testGetType()
    {
        $type = new Symfony_Component_Form_Tests_Fixtures_FooType();
        $resolvedType = $this->getMockResolvedType();

        $resolvedType->expects($this->once())
            ->method('getInnerType')
            ->will($this->returnValue($type));

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('name')
            ->will($this->returnValue($resolvedType));

        set_error_handler(array('Symfony_Component_Form_Test_DeprecationErrorHandler', 'handle'));
        $this->assertEquals($type, $this->factory->getType('name'));
        restore_error_handler();
    }

    public function testCreateNamedBuilderWithTypeName()
    {
        $options = array('a' => '1', 'b' => '2');
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('type')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $options)
            ->will($this->returnValue('BUILDER'));

        $this->assertSame('BUILDER', $this->factory->createNamedBuilder('name', 'type', null, $options));
    }

    public function testCreateNamedBuilderWithTypeInstance()
    {
        $options = array('a' => '1', 'b' => '2');
        $type = new Symfony_Component_Form_Tests_Fixtures_FooType();
        $resolvedType = $this->getMockResolvedType();

        $this->resolvedTypeFactory->expects($this->once())
            ->method('createResolvedType')
            ->with($type)
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $options)
            ->will($this->returnValue('BUILDER'));

        $this->assertSame('BUILDER', $this->factory->createNamedBuilder('name', $type, null, $options));
    }

    public function testCreateNamedBuilderWithTypeInstanceWithParentType()
    {
        $options = array('a' => '1', 'b' => '2');
        $type = new Symfony_Component_Form_Tests_Fixtures_FooSubType();
        $resolvedType = $this->getMockResolvedType();
        $parentResolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('foo')
            ->will($this->returnValue($parentResolvedType));

        $this->resolvedTypeFactory->expects($this->once())
            ->method('createResolvedType')
            ->with($type, array(), $parentResolvedType)
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $options)
            ->will($this->returnValue('BUILDER'));

        $this->assertSame('BUILDER', $this->factory->createNamedBuilder('name', $type, null, $options));
    }

    public function testCreateNamedBuilderWithTypeInstanceWithParentTypeInstance()
    {
        $options = array('a' => '1', 'b' => '2');
        $type = new Symfony_Component_Form_Tests_Fixtures_FooSubTypeWithParentInstance();
        $resolvedType = $this->getMockResolvedType();
        $parentResolvedType = $this->getMockResolvedType();

        $this->resolvedTypeFactory->expects($this->at(0))
            ->method('createResolvedType')
            ->with($type->getParent())
            ->will($this->returnValue($parentResolvedType));

        $this->resolvedTypeFactory->expects($this->at(1))
            ->method('createResolvedType')
            ->with($type, array(), $parentResolvedType)
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $options)
            ->will($this->returnValue('BUILDER'));

        $this->assertSame('BUILDER', $this->factory->createNamedBuilder('name', $type, null, $options));
    }

    public function testCreateNamedBuilderWithResolvedTypeInstance()
    {
        $options = array('a' => '1', 'b' => '2');
        $resolvedType = $this->getMockResolvedType();

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $options)
            ->will($this->returnValue('BUILDER'));

        $this->assertSame('BUILDER', $this->factory->createNamedBuilder('name', $resolvedType, null, $options));
    }

    public function testCreateNamedBuilderWithParentBuilder()
    {
        $options = array('a' => '1', 'b' => '2');
        $parentBuilder = $this->getMockFormBuilder();
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('type')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $options, $parentBuilder)
            ->will($this->returnValue('BUILDER'));

        $this->assertSame('BUILDER', $this->factory->createNamedBuilder('name', 'type', null, $options, $parentBuilder));
    }

    public function testCreateNamedBuilderFillsDataOption()
    {
        $givenOptions = array('a' => '1', 'b' => '2');
        $expectedOptions = array_merge($givenOptions, array('data' => 'DATA'));
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('type')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $expectedOptions)
            ->will($this->returnValue('BUILDER'));

        $this->assertSame('BUILDER', $this->factory->createNamedBuilder('name', 'type', 'DATA', $givenOptions));
    }

    public function testCreateNamedBuilderDoesNotOverrideExistingDataOption()
    {
        $options = array('a' => '1', 'b' => '2', 'data' => 'CUSTOM');
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('type')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $options)
            ->will($this->returnValue('BUILDER'));

        $this->assertSame('BUILDER', $this->factory->createNamedBuilder('name', 'type', 'DATA', $options));
    }

    /**
     * @expectedException        Symfony_Component_Form_Exception_UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "string, Symfony_Component_Form_ResolvedFormTypeInterface or Symfony_Component_Form_FormTypeInterface", "stdClass" given
     */
    public function testCreateNamedBuilderThrowsUnderstandableException()
    {
        $this->factory->createNamedBuilder('name', new stdClass());
    }

    public function testCreateUsesTypeNameIfTypeGivenAsString()
    {
        $options = array('a' => '1', 'b' => '2');
        $resolvedType = $this->getMockResolvedType();
        $builder = $this->getMockFormBuilder();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('TYPE')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'TYPE', $options)
            ->will($this->returnValue($builder));

        $builder->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue('FORM'));

        $this->assertSame('FORM', $this->factory->create('TYPE', null, $options));
    }

    public function testCreateUsesTypeNameIfTypeGivenAsObject()
    {
        $options = array('a' => '1', 'b' => '2');
        $resolvedType = $this->getMockResolvedType();
        $builder = $this->getMockFormBuilder();

        $resolvedType->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('TYPE'));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'TYPE', $options)
            ->will($this->returnValue($builder));

        $builder->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue('FORM'));

        $this->assertSame('FORM', $this->factory->create($resolvedType, null, $options));
    }

    public function testCreateNamed()
    {
        $options = array('a' => '1', 'b' => '2');
        $resolvedType = $this->getMockResolvedType();
        $builder = $this->getMockFormBuilder();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('type')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $options)
            ->will($this->returnValue($builder));

        $builder->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue('FORM'));

        $this->assertSame('FORM', $this->factory->createNamed('name', 'type', null, $options));
    }

    public function testCreateBuilderForPropertyWithoutTypeGuesser()
    {
        $registry = $this->getMock('Symfony_Component_Form_FormRegistryInterface');
        $factory = $this->getMockBuilder('Symfony_Component_Form_FormFactory')
            ->setMethods(array('createNamedBuilder'))
            ->setConstructorArgs(array($registry, $this->resolvedTypeFactory))
            ->getMock();

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', 'text', null, array())
            ->will($this->returnValue('builderInstance'));

        $builder = $factory->createBuilderForProperty('Application_Author', 'firstName');

        $this->assertEquals('builderInstance', $builder);
    }

    public function testCreateBuilderForPropertyCreatesFormWithHighestConfidence()
    {
        $this->guesser1->expects($this->once())
            ->method('guessType')
            ->with('Application_Author', 'firstName')
            ->will($this->returnValue(new Symfony_Component_Form_Guess_TypeGuess(
                'text',
                array('max_length' => 10),
                Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE
            )));

        $this->guesser2->expects($this->once())
            ->method('guessType')
            ->with('Application_Author', 'firstName')
            ->will($this->returnValue(new Symfony_Component_Form_Guess_TypeGuess(
                'password',
                array('max_length' => 7),
                Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE
            )));

        $factory = $this->getMockFactory(array('createNamedBuilder'));

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', 'password', null, array('max_length' => 7))
            ->will($this->returnValue('builderInstance'));

        $builder = $factory->createBuilderForProperty('Application_Author', 'firstName');

        $this->assertEquals('builderInstance', $builder);
    }

    public function testCreateBuilderCreatesTextFormIfNoGuess()
    {
        $this->guesser1->expects($this->once())
                ->method('guessType')
                ->with('Application_Author', 'firstName')
                ->will($this->returnValue(null));

        $factory = $this->getMockFactory(array('createNamedBuilder'));

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', 'text')
            ->will($this->returnValue('builderInstance'));

        $builder = $factory->createBuilderForProperty('Application_Author', 'firstName');

        $this->assertEquals('builderInstance', $builder);
    }

    public function testOptionsCanBeOverridden()
    {
        $this->guesser1->expects($this->once())
                ->method('guessType')
                ->with('Application_Author', 'firstName')
                ->will($this->returnValue(new Symfony_Component_Form_Guess_TypeGuess(
                    'text',
                    array('max_length' => 10),
                    Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE
                )));

        $factory = $this->getMockFactory(array('createNamedBuilder'));

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', 'text', null, array('max_length' => 11))
            ->will($this->returnValue('builderInstance'));

        $builder = $factory->createBuilderForProperty(
            'Application_Author',
            'firstName',
            null,
            array('max_length' => 11)
        );

        $this->assertEquals('builderInstance', $builder);
    }

    public function testCreateBuilderUsesMaxLengthIfFound()
    {
        $this->guesser1->expects($this->once())
                ->method('guessMaxLength')
                ->with('Application_Author', 'firstName')
                ->will($this->returnValue(new Symfony_Component_Form_Guess_ValueGuess(
                    15,
                    Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE
                )));

        $this->guesser2->expects($this->once())
                ->method('guessMaxLength')
                ->with('Application_Author', 'firstName')
                ->will($this->returnValue(new Symfony_Component_Form_Guess_ValueGuess(
                    20,
                    Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE
                )));

        $factory = $this->getMockFactory(array('createNamedBuilder'));

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', 'text', null, array('max_length' => 20))
            ->will($this->returnValue('builderInstance'));

        $builder = $factory->createBuilderForProperty(
            'Application_Author',
            'firstName'
        );

        $this->assertEquals('builderInstance', $builder);
    }

    public function testCreateBuilderUsesMinLengthIfFound()
    {
        $this->guesser1->expects($this->once())
                ->method('guessMinLength')
                ->with('Application_Author', 'firstName')
                ->will($this->returnValue(new Symfony_Component_Form_Guess_ValueGuess(
                    2,
                    Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE
                )));

        $this->guesser2->expects($this->once())
                ->method('guessMinLength')
                ->with('Application_Author', 'firstName')
                ->will($this->returnValue(new Symfony_Component_Form_Guess_ValueGuess(
                    5,
                    Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE
                )));

        $factory = $this->getMockFactory(array('createNamedBuilder'));

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', 'text', null, array('pattern' => '.{5,}'))
            ->will($this->returnValue('builderInstance'));

        $builder = $factory->createBuilderForProperty(
            'Application_Author',
            'firstName'
        );

        $this->assertEquals('builderInstance', $builder);
    }

    public function testCreateBuilderPrefersPatternOverMinLength()
    {
        // min length is deprecated
        $this->guesser1->expects($this->once())
                ->method('guessMinLength')
                ->with('Application_Author', 'firstName')
                ->will($this->returnValue(new Symfony_Component_Form_Guess_ValueGuess(
                    2,
                    Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE
                )));

        // pattern is preferred even though confidence is lower
        $this->guesser2->expects($this->once())
                ->method('guessPattern')
                ->with('Application_Author', 'firstName')
                ->will($this->returnValue(new Symfony_Component_Form_Guess_ValueGuess(
                    '.{5,10}',
                    Symfony_Component_Form_Guess_Guess::LOW_CONFIDENCE
                )));

        $factory = $this->getMockFactory(array('createNamedBuilder'));

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', 'text', null, array('pattern' => '.{5,10}'))
            ->will($this->returnValue('builderInstance'));

        $builder = $factory->createBuilderForProperty(
            'Application_Author',
            'firstName'
        );

        $this->assertEquals('builderInstance', $builder);
    }

    public function testCreateBuilderUsesRequiredSettingWithHighestConfidence()
    {
        $this->guesser1->expects($this->once())
                ->method('guessRequired')
                ->with('Application_Author', 'firstName')
                ->will($this->returnValue(new Symfony_Component_Form_Guess_ValueGuess(
                    true,
                    Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE
                )));

        $this->guesser2->expects($this->once())
                ->method('guessRequired')
                ->with('Application_Author', 'firstName')
                ->will($this->returnValue(new Symfony_Component_Form_Guess_ValueGuess(
                    false,
                    Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE
                )));

        $factory = $this->getMockFactory(array('createNamedBuilder'));

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', 'text', null, array('required' => false))
            ->will($this->returnValue('builderInstance'));

        $builder = $factory->createBuilderForProperty(
            'Application_Author',
            'firstName'
        );

        $this->assertEquals('builderInstance', $builder);
    }

    public function testCreateBuilderUsesPatternIfFound()
    {
        $this->guesser1->expects($this->once())
                ->method('guessPattern')
                ->with('Application_Author', 'firstName')
                ->will($this->returnValue(new Symfony_Component_Form_Guess_ValueGuess(
                    '[a-z]',
                    Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE
                )));

        $this->guesser2->expects($this->once())
                ->method('guessPattern')
                ->with('Application_Author', 'firstName')
                ->will($this->returnValue(new Symfony_Component_Form_Guess_ValueGuess(
                    '[a-zA-Z]',
                    Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE
                )));

        $factory = $this->getMockFactory(array('createNamedBuilder'));

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', 'text', null, array('pattern' => '[a-zA-Z]'))
            ->will($this->returnValue('builderInstance'));

        $builder = $factory->createBuilderForProperty(
            'Application_Author',
            'firstName'
        );

        $this->assertEquals('builderInstance', $builder);
    }

    private function getMockFactory(array $methods = array())
    {
        return $this->getMockBuilder('Symfony_Component_Form_FormFactory')
            ->setMethods($methods)
            ->setConstructorArgs(array($this->registry, $this->resolvedTypeFactory))
            ->getMock();
    }

    private function getMockResolvedType()
    {
        return $this->getMock('Symfony_Component_Form_ResolvedFormTypeInterface');
    }

    private function getMockType()
    {
        return $this->getMock('Symfony_Component_Form_FormTypeInterface');
    }

    private function getMockFormBuilder()
    {
        return $this->getMock('Symfony_Component_Form_Test_FormBuilderInterface');
    }
}
