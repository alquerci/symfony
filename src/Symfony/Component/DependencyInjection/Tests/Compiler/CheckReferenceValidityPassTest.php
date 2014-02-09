<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_Compiler_CheckReferenceValidityPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcessIgnoresScopeWideningIfNonStrictReference()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->register('a')->addArgument(new Symfony_Component_DependencyInjection_Reference('b', Symfony_Component_DependencyInjection_ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, false));
        $container->register('b')->setScope('prototype');

        $this->process($container);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testProcessDetectsScopeWidening()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->register('a')->addArgument(new Symfony_Component_DependencyInjection_Reference('b'));
        $container->register('b')->setScope('prototype');

        $this->process($container);
    }

    public function testProcessIgnoresCrossScopeHierarchyReferenceIfNotStrict()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->addScope(new Symfony_Component_DependencyInjection_Scope('a'));
        $container->addScope(new Symfony_Component_DependencyInjection_Scope('b'));

        $container->register('a')->setScope('a')->addArgument(new Symfony_Component_DependencyInjection_Reference('b', Symfony_Component_DependencyInjection_ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, false));
        $container->register('b')->setScope('b');

        $this->process($container);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testProcessDetectsCrossScopeHierarchyReference()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->addScope(new Symfony_Component_DependencyInjection_Scope('a'));
        $container->addScope(new Symfony_Component_DependencyInjection_Scope('b'));

        $container->register('a')->setScope('a')->addArgument(new Symfony_Component_DependencyInjection_Reference('b'));
        $container->register('b')->setScope('b');

        $this->process($container);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testProcessDetectsReferenceToAbstractDefinition()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();

        $container->register('a')->setAbstract(true);
        $container->register('b')->addArgument(new Symfony_Component_DependencyInjection_Reference('a'));

        $this->process($container);
    }

    public function testProcess()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->register('a')->addArgument(new Symfony_Component_DependencyInjection_Reference('b'));
        $container->register('b');

        $this->process($container);
    }

    protected function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $pass = new Symfony_Component_DependencyInjection_Compiler_CheckReferenceValidityPass();
        $pass->process($container);
    }
}
