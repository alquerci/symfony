<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_Compiler_CheckDefinitionValidityPassTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testProcessDetectsSyntheticNonPublicDefinitions()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->register('a')->setSynthetic(true)->setPublic(false);

        $this->process($container);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testProcessDetectsSyntheticPrototypeDefinitions()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->register('a')->setSynthetic(true)->setScope(Symfony_Component_DependencyInjection_ContainerInterface::SCOPE_PROTOTYPE);

        $this->process($container);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testProcessDetectsNonSyntheticNonAbstractDefinitionWithoutClass()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->register('a')->setSynthetic(false)->setAbstract(false);

        $this->process($container);
    }

    public function testProcess()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->register('a', 'class');
        $container->register('b', 'class')->setSynthetic(true)->setPublic(true);
        $container->register('c', 'class')->setAbstract(true);
        $container->register('d', 'class')->setSynthetic(true);

        $this->process($container);
    }

    protected function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $pass = new Symfony_Component_DependencyInjection_Compiler_CheckDefinitionValidityPass();
        $pass->process($container);
    }
}
