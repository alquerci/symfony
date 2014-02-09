<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_Compiler_CheckCircularReferencesPassTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testProcess()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->register('a')->addArgument(new Symfony_Component_DependencyInjection_Reference('b'));
        $container->register('b')->addArgument(new Symfony_Component_DependencyInjection_Reference('a'));

        $this->process($container);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testProcessWithAliases()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->register('a')->addArgument(new Symfony_Component_DependencyInjection_Reference('b'));
        $container->setAlias('b', 'c');
        $container->setAlias('c', 'a');

        $this->process($container);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testProcessDetectsIndirectCircularReference()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->register('a')->addArgument(new Symfony_Component_DependencyInjection_Reference('b'));
        $container->register('b')->addArgument(new Symfony_Component_DependencyInjection_Reference('c'));
        $container->register('c')->addArgument(new Symfony_Component_DependencyInjection_Reference('a'));

        $this->process($container);
    }

    public function testProcessIgnoresMethodCalls()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->register('a')->addArgument(new Symfony_Component_DependencyInjection_Reference('b'));
        $container->register('b')->addMethodCall('setA', array(new Symfony_Component_DependencyInjection_Reference('a')));

        $this->process($container);
    }

    protected function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $compiler = new Symfony_Component_DependencyInjection_Compiler_Compiler();
        $passConfig = $compiler->getPassConfig();
        $passConfig->setOptimizationPasses(array(
            new Symfony_Component_DependencyInjection_Compiler_AnalyzeServiceReferencesPass(true),
            new Symfony_Component_DependencyInjection_Compiler_CheckCircularReferencesPass(),
        ));
        $passConfig->setRemovingPasses(array());

        $compiler->compile($container);
    }
}
