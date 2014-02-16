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
class Symfony_Component_Form_Tests_ResolvedFormTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $dispatcher;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $dataMapper;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_OptionsResolver_OptionsResolver')) {
            $this->markTestSkipped('The "OptionsResolver" component is not available');
        }

        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        $this->dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $this->factory = $this->getMock('Symfony_Component_Form_FormFactoryInterface');
        $this->dataMapper = $this->getMock('Symfony_Component_Form_DataMapperInterface');
    }

    public function testCreateBuilder()
    {
        if (version_compare(PHPUnit_Runner_Version::id(), '3.7', '<')) {
            $this->markTestSkipped('This test requires PHPUnit 3.7.');
        }

        $parentType = $this->getMockFormType();
        $type = $this->getMockFormType();
        $extension1 = $this->getMockFormTypeExtension();
        $extension2 = $this->getMockFormTypeExtension();

        $parentResolvedType = new Symfony_Component_Form_ResolvedFormType($parentType);
        $resolvedType = new Symfony_Component_Form_ResolvedFormType($type, array($extension1, $extension2), $parentResolvedType);

        $test = $this;
        $i = 0;

        $assertIndex = array(
            new Symfony_Component_Form_Tests_ResolvedFormTypeTestAssertIndexClosure($i, $test),
            'build'
        );

        $assertIndexAndAddOption = array(
            new Symfony_Component_Form_Tests_ResolvedFormTypeTestAssertIndexClosure($assertIndex),
            'build'
        );

        // First the default options are generated for the super type
        $parentType->expects($this->once())
            ->method('setDefaultOptions')
            ->will($this->returnCallback(call_user_func($assertIndexAndAddOption, 0, 'a', 'a_default')));

        // The form type itself
        $type->expects($this->once())
            ->method('setDefaultOptions')
            ->will($this->returnCallback(call_user_func($assertIndexAndAddOption, 1, 'b', 'b_default')));

        // And its extensions
        $extension1->expects($this->once())
            ->method('setDefaultOptions')
            ->will($this->returnCallback(call_user_func($assertIndexAndAddOption, 2, 'c', 'c_default')));

        $extension2->expects($this->once())
            ->method('setDefaultOptions')
            ->will($this->returnCallback(call_user_func($assertIndexAndAddOption, 3, 'd', 'd_default')));

        $givenOptions = array('a' => 'a_custom', 'c' => 'c_custom');
        $resolvedOptions = array('a' => 'a_custom', 'b' => 'b_default', 'c' => 'c_custom', 'd' => 'd_default');

        // Then the form is built for the super type
        $parentType->expects($this->once())
            ->method('buildForm')
            ->with($this->anything(), $resolvedOptions)
            ->will($this->returnCallback(call_user_func($assertIndex, 4)));

        // Then the type itself
        $type->expects($this->once())
            ->method('buildForm')
            ->with($this->anything(), $resolvedOptions)
            ->will($this->returnCallback(call_user_func($assertIndex, 5)));

        // Then its extensions
        $extension1->expects($this->once())
            ->method('buildForm')
            ->with($this->anything(), $resolvedOptions)
            ->will($this->returnCallback(call_user_func($assertIndex, 6)));

        $extension2->expects($this->once())
            ->method('buildForm')
            ->with($this->anything(), $resolvedOptions)
            ->will($this->returnCallback(call_user_func($assertIndex, 7)));

        $factory = $this->getMockFormFactory();
        $parentBuilder = $this->getBuilder('parent');
        $builder = $resolvedType->createBuilder($factory, 'name', $givenOptions, $parentBuilder);

        $this->assertSame($parentBuilder, $builder->getParent());
        $this->assertSame($resolvedType, $builder->getType());
    }

    public function testCreateView()
    {
        $parentType = $this->getMockFormType();
        $type = $this->getMockFormType();
        $field1Type = $this->getMockFormType();
        $field2Type = $this->getMockFormType();
        $extension1 = $this->getMockFormTypeExtension();
        $extension2 = $this->getMockFormTypeExtension();

        $parentResolvedType = new Symfony_Component_Form_ResolvedFormType($parentType);
        $resolvedType = new Symfony_Component_Form_ResolvedFormType($type, array($extension1, $extension2), $parentResolvedType);
        $field1ResolvedType = new Symfony_Component_Form_ResolvedFormType($field1Type);
        $field2ResolvedType = new Symfony_Component_Form_ResolvedFormType($field2Type);

        $options = array('a' => '1', 'b' => '2');
        $form = $this->getBuilder('name', $options)
            ->setCompound(true)
            ->setDataMapper($this->dataMapper)
            ->setType($resolvedType)
            ->add($this->getBuilder('foo')->setType($field1ResolvedType))
            ->add($this->getBuilder('bar')->setType($field2ResolvedType))
            ->getForm();

        $test = $this;
        $i = 0;

        $assertIndexAndNbOfChildViews = array(
            new Symfony_Component_Form_Tests_ResolvedFormTypeTestAssertIndexAndNbOfChildViewsClosure($i, $test),
            'build'
        );

        // First the super type
        $parentType->expects($this->once())
            ->method('buildView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback(call_user_func($assertIndexAndNbOfChildViews, 0, 0)));

        // Then the type itself
        $type->expects($this->once())
            ->method('buildView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback(call_user_func($assertIndexAndNbOfChildViews, 1, 0)));

        // Then its extensions
        $extension1->expects($this->once())
            ->method('buildView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback(call_user_func($assertIndexAndNbOfChildViews, 2, 0)));

        $extension2->expects($this->once())
            ->method('buildView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback(call_user_func($assertIndexAndNbOfChildViews, 3, 0)));

        // Now the first child form
        $field1Type->expects($this->once())
            ->method('buildView')
            ->will($this->returnCallback(call_user_func($assertIndexAndNbOfChildViews, 4, 0)));
        $field1Type->expects($this->once())
            ->method('finishView')
            ->will($this->returnCallback(call_user_func($assertIndexAndNbOfChildViews, 5, 0)));

        // And the second child form
        $field2Type->expects($this->once())
            ->method('buildView')
            ->will($this->returnCallback(call_user_func($assertIndexAndNbOfChildViews, 6, 0)));
        $field2Type->expects($this->once())
            ->method('finishView')
            ->will($this->returnCallback(call_user_func($assertIndexAndNbOfChildViews, 7, 0)));

        // Again first the parent
        $parentType->expects($this->once())
            ->method('finishView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback(call_user_func($assertIndexAndNbOfChildViews, 8, 2)));

        // Then the type itself
        $type->expects($this->once())
            ->method('finishView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback(call_user_func($assertIndexAndNbOfChildViews, 9, 2)));

        // Then its extensions
        $extension1->expects($this->once())
            ->method('finishView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback(call_user_func($assertIndexAndNbOfChildViews, 10, 2)));

        $extension2->expects($this->once())
            ->method('finishView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback(call_user_func($assertIndexAndNbOfChildViews, 11, 2)));

        $parentView = new Symfony_Component_Form_FormView();
        $view = $resolvedType->createView($form, $parentView);

        $this->assertSame($parentView, $view->parent);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockFormType()
    {
        return $this->getMock('Symfony_Component_Form_FormTypeInterface');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockFormTypeExtension()
    {
        return $this->getMock('Symfony_Component_Form_FormTypeExtensionInterface');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockFormFactory()
    {
        return $this->getMock('Symfony_Component_Form_FormFactoryInterface');
    }

    /**
     * @param string $name
     * @param array $options
     *
     * @return Symfony_Component_Form_FormBuilder
     */
    protected function getBuilder($name = 'name', array $options = array())
    {
        return new Symfony_Component_Form_FormBuilder($name, null, $this->dispatcher, $this->factory, $options);
    }
}

class Symfony_Component_Form_Tests_ResolvedFormTypeTestAssertIndexClosure
{
    private $i;
    private $index;

    /**
     * @var PHPUnit_Framework_TestCase
     */
    private $test;

    public function __construct(&$i, PHPUnit_Framework_TestCase $test, $index = null)
    {
        $this->i = &$i;
        $this->test = $test;
        $this->index = $index;
    }

    public function build($index)
    {
        return array(new self($this->i, $this->test, $index), 'assertIndex');
    }

    public function assertIndex()
    {
        /* @var PHPUnit_Framework_TestCase $test */
        $this->test->assertEquals($this->index, $this->i, 'Executed at index ' . $this->index);

        ++$this->i;
    }
}

class Symfony_Component_Form_Tests_ResolvedFormTypeTestAssertIndexAndAddOptionClosure
{
    private $assertIndex;
    private $index;
    private $option;
    private $default;

    public function __construct($assertIndex, $index = null, $option = null, $default = null)
    {
        $this->assertIndex = $assertIndex;
        $this->index = $index;
        $this->option = $option;
        $this->default = $default;
    }

    public function build($index, $option, $default)
    {
        $assertIndex = call_user_func($this->assertIndex, $index);

        return array(
            new self($assertIndex, $index, $option, $default),
            'assertIndexAndAddOption'
        );
    }

    public function assertIndexAndAddOption(Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver)
    {
        call_user_func($this->assertIndex);

        $resolver->setDefaults(array($this->option => $this->default));
    }
}

class Symfony_Component_Form_Tests_ResolvedFormTypeTestAssertIndexAndNbOfChildViewsClosure
{
    private $i;
    private $index;
    private $nbOfChildViews;

    /**
     * @var PHPUnit_Framework_TestCase
     */
    private $test;

    public function __construct(&$i, PHPUnit_Framework_TestCase $test, $index = null, $nbOfChildViews = null)
    {
        $this->i = &$i;
        $this->test = $test;
        $this->index = $index;
        $this->nbOfChildViews = $nbOfChildViews;
    }

    public function build($index, $nbOfChildViews)
    {
        return array(
            new self($this->i, $this->test, $index, $nbOfChildViews),
            'assertIndexAndNbOfChildViews'
        );
    }

    public function assertIndexAndNbOfChildViews(Symfony_Component_Form_FormView $view)
    {
        $this->test->assertEquals($this->index, $this->i, 'Executed at index ' . $this->index);
        $this->test->assertCount($this->nbOfChildViews, $view);

        ++$this->i;
    }
}
