<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_Compiler_ResolveInvalidReferencesPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $def = $container
            ->register('foo')
            ->setArguments(array(new Symfony_Component_DependencyInjection_Reference('bar', Symfony_Component_DependencyInjection_ContainerInterface::NULL_ON_INVALID_REFERENCE)))
            ->addMethodCall('foo', array(new Symfony_Component_DependencyInjection_Reference('moo', Symfony_Component_DependencyInjection_ContainerInterface::IGNORE_ON_INVALID_REFERENCE)))
        ;

        $this->process($container);

        $arguments = $def->getArguments();
        $this->assertNull($arguments[0]);
        $this->assertCount(0, $def->getMethodCalls());
    }

    public function testProcessIgnoreNonExistentServices()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $def = $container
            ->register('foo')
            ->setArguments(array(new Symfony_Component_DependencyInjection_Reference('bar')))
        ;

        $this->process($container);

        $arguments = $def->getArguments();
        $this->assertEquals('bar', (string) $arguments[0]->__toString());
    }

    public function testProcessRemovesPropertiesOnInvalid()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $def = $container
            ->register('foo')
            ->setProperty('foo', new Symfony_Component_DependencyInjection_Reference('bar', Symfony_Component_DependencyInjection_ContainerInterface::IGNORE_ON_INVALID_REFERENCE))
        ;

        $this->process($container);

        $this->assertEquals(array(), $def->getProperties());
    }

    public function testStrictFlagIsPreserved()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->register('bar');
        $def = $container
            ->register('foo')
            ->addArgument(new Symfony_Component_DependencyInjection_Reference('bar', Symfony_Component_DependencyInjection_ContainerInterface::NULL_ON_INVALID_REFERENCE, false))
        ;

        $this->process($container);

        $this->assertFalse($def->getArgument(0)->isStrict());
    }

    protected function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $pass = new Symfony_Component_DependencyInjection_Compiler_ResolveInvalidReferencesPass();
        $pass->process($container);
    }
}
