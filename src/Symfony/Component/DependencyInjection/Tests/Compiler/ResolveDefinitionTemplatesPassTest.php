<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_Compiler_ResolveDefinitionTemplatesPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->register('parent', 'foo')->setArguments(array('moo', 'b'))->setProperty('foo', 'moo');
        $container->setDefinition('child', new Symfony_Component_DependencyInjection_DefinitionDecorator('parent'))
            ->replaceArgument(0, 'a')
            ->setProperty('foo', 'bar')
            ->setClass('bar')
        ;

        $this->process($container);

        $def = $container->getDefinition('child');
        $this->assertNotInstanceOf('Symfony_Component_DependencyInjection_DefinitionDecorator', $def);
        $this->assertEquals('bar', $def->getClass());
        $this->assertEquals(array('a', 'b'), $def->getArguments());
        $this->assertEquals(array('foo' => 'bar'), $def->getProperties());
    }

    public function testProcessAppendsMethodCallsAlways()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();

        $container
            ->register('parent')
            ->addMethodCall('foo', array('bar'))
        ;

        $container
            ->setDefinition('child', new Symfony_Component_DependencyInjection_DefinitionDecorator('parent'))
            ->addMethodCall('bar', array('foo'))
        ;

        $this->process($container);

        $def = $container->getDefinition('child');
        $this->assertEquals(array(
            array('foo', array('bar')),
            array('bar', array('foo')),
        ), $def->getMethodCalls());
    }

    public function testProcessDoesNotCopyAbstract()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();

        $container
            ->register('parent')
            ->setAbstract(true)
        ;

        $container
            ->setDefinition('child', new Symfony_Component_DependencyInjection_DefinitionDecorator('parent'))
        ;

        $this->process($container);

        $def = $container->getDefinition('child');
        $this->assertFalse($def->isAbstract());
    }

    public function testProcessDoesNotCopyScope()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();

        $container
            ->register('parent')
            ->setScope('foo')
        ;

        $container
            ->setDefinition('child', new Symfony_Component_DependencyInjection_DefinitionDecorator('parent'))
        ;

        $this->process($container);

        $def = $container->getDefinition('child');
        $this->assertEquals(Symfony_Component_DependencyInjection_ContainerInterface::SCOPE_CONTAINER, $def->getScope());
    }

    public function testProcessDoesNotCopyTags()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();

        $container
            ->register('parent')
            ->addTag('foo')
        ;

        $container
            ->setDefinition('child', new Symfony_Component_DependencyInjection_DefinitionDecorator('parent'))
        ;

        $this->process($container);

        $def = $container->getDefinition('child');
        $this->assertEquals(array(), $def->getTags());
    }

    public function testProcessHandlesMultipleInheritance()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();

        $container
            ->register('parent', 'foo')
            ->setArguments(array('foo', 'bar', 'c'))
        ;

        $container
            ->setDefinition('child2', new Symfony_Component_DependencyInjection_DefinitionDecorator('child1'))
            ->replaceArgument(1, 'b')
        ;

        $container
            ->setDefinition('child1', new Symfony_Component_DependencyInjection_DefinitionDecorator('parent'))
            ->replaceArgument(0, 'a')
        ;

        $this->process($container);

        $def = $container->getDefinition('child2');
        $this->assertEquals(array('a', 'b', 'c'), $def->getArguments());
        $this->assertEquals('foo', $def->getClass());
    }

    protected function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $pass = new Symfony_Component_DependencyInjection_Compiler_ResolveDefinitionTemplatesPass();
        $pass->process($container);
    }
}
