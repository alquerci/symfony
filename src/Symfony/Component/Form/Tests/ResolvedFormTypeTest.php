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

        $assertIndex = function ($index) use (&$i, $test) {
            return function () use (&$i, $test, $index) {
                /* @var PHPUnit_Framework_TestCase $test */
                $test->assertEquals($index, $i, 'Executed at index ' . $index);

                ++$i;
            };
        };

        $assertIndexAndAddOption = function ($index, $option, $default) use ($assertIndex) {
            $assertIndex = $assertIndex($index);

            return function (Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver) use ($assertIndex, $index, $option, $default) {
                $assertIndex();

                $resolver->setDefaults(array($option => $default));
            };
        };

        // First the default options are generated for the super type
        $parentType->expects($this->once())
            ->method('setDefaultOptions')
            ->will($this->returnCallback($assertIndexAndAddOption(0, 'a', 'a_default')));

        // The form type itself
        $type->expects($this->once())
            ->method('setDefaultOptions')
            ->will($this->returnCallback($assertIndexAndAddOption(1, 'b', 'b_default')));

        // And its extensions
        $extension1->expects($this->once())
            ->method('setDefaultOptions')
            ->will($this->returnCallback($assertIndexAndAddOption(2, 'c', 'c_default')));

        $extension2->expects($this->once())
            ->method('setDefaultOptions')
            ->will($this->returnCallback($assertIndexAndAddOption(3, 'd', 'd_default')));

        $givenOptions = array('a' => 'a_custom', 'c' => 'c_custom');
        $resolvedOptions = array('a' => 'a_custom', 'b' => 'b_default', 'c' => 'c_custom', 'd' => 'd_default');

        // Then the form is built for the super type
        $parentType->expects($this->once())
            ->method('buildForm')
            ->with($this->anything(), $resolvedOptions)
            ->will($this->returnCallback($assertIndex(4)));

        // Then the type itself
        $type->expects($this->once())
            ->method('buildForm')
            ->with($this->anything(), $resolvedOptions)
            ->will($this->returnCallback($assertIndex(5)));

        // Then its extensions
        $extension1->expects($this->once())
            ->method('buildForm')
            ->with($this->anything(), $resolvedOptions)
            ->will($this->returnCallback($assertIndex(6)));

        $extension2->expects($this->once())
            ->method('buildForm')
            ->with($this->anything(), $resolvedOptions)
            ->will($this->returnCallback($assertIndex(7)));

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

        $assertIndexAndNbOfChildViews = function ($index, $nbOfChildViews) use (&$i, $test) {
            return function (Symfony_Component_Form_FormView $view) use (&$i, $test, $index, $nbOfChildViews) {
                /* @var PHPUnit_Framework_TestCase $test */
                $test->assertEquals($index, $i, 'Executed at index ' . $index);
                $test->assertCount($nbOfChildViews, $view);

                ++$i;
            };
        };

        // First the super type
        $parentType->expects($this->once())
            ->method('buildView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback($assertIndexAndNbOfChildViews(0, 0)));

        // Then the type itself
        $type->expects($this->once())
            ->method('buildView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback($assertIndexAndNbOfChildViews(1, 0)));

        // Then its extensions
        $extension1->expects($this->once())
            ->method('buildView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback($assertIndexAndNbOfChildViews(2, 0)));

        $extension2->expects($this->once())
            ->method('buildView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback($assertIndexAndNbOfChildViews(3, 0)));

        // Now the first child form
        $field1Type->expects($this->once())
            ->method('buildView')
            ->will($this->returnCallback($assertIndexAndNbOfChildViews(4, 0)));
        $field1Type->expects($this->once())
            ->method('finishView')
            ->will($this->returnCallback($assertIndexAndNbOfChildViews(5, 0)));

        // And the second child form
        $field2Type->expects($this->once())
            ->method('buildView')
            ->will($this->returnCallback($assertIndexAndNbOfChildViews(6, 0)));
        $field2Type->expects($this->once())
            ->method('finishView')
            ->will($this->returnCallback($assertIndexAndNbOfChildViews(7, 0)));

        // Again first the parent
        $parentType->expects($this->once())
            ->method('finishView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback($assertIndexAndNbOfChildViews(8, 2)));

        // Then the type itself
        $type->expects($this->once())
            ->method('finishView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback($assertIndexAndNbOfChildViews(9, 2)));

        // Then its extensions
        $extension1->expects($this->once())
            ->method('finishView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback($assertIndexAndNbOfChildViews(10, 2)));

        $extension2->expects($this->once())
            ->method('finishView')
            ->with($this->anything(), $form, $options)
            ->will($this->returnCallback($assertIndexAndNbOfChildViews(11, 2)));

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
