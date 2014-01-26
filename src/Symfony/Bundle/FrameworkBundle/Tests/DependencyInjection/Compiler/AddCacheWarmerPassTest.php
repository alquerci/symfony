<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_DependencyInjection_Compiler_AddCacheWarmerPassTest extends PHPUnit_Framework_TestCase
{
    public function testThatCacheWarmersAreProcessedInPriorityOrder()
    {
        $services = array(
            'my_cache_warmer_service1' => array(0 => array('priority' => 100)),
            'my_cache_warmer_service2' => array(0 => array('priority' => 200)),
            'my_cache_warmer_service3' => array()
        );

        $definition = $this->getMock('Symfony_Component_DependencyInjection_Definition');
        $container = $this->getMock('Symfony_Component_DependencyInjection_ContainerBuilder');

        $container->expects($this->atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will($this->returnValue($services));
        $container->expects($this->atLeastOnce())
            ->method('getDefinition')
            ->with('cache_warmer')
            ->will($this->returnValue($definition));
        $container->expects($this->atLeastOnce())
            ->method('hasDefinition')
            ->with('cache_warmer')
            ->will($this->returnValue(true));

        $definition->expects($this->once())
            ->method('replaceArgument')
            ->with(0, array(
                new Symfony_Component_DependencyInjection_Reference('my_cache_warmer_service2'),
                new Symfony_Component_DependencyInjection_Reference('my_cache_warmer_service1'),
                new Symfony_Component_DependencyInjection_Reference('my_cache_warmer_service3')
            ));

        $addCacheWarmerPass = new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_AddCacheWarmerPass();
        $addCacheWarmerPass->process($container);
    }

    public function testThatCompilerPassIsIgnoredIfThereIsNoCacheWarmerDefinition()
    {
        $definition = $this->getMock('Symfony_Component_DependencyInjection_Definition');
        $container = $this->getMock('Symfony_Component_DependencyInjection_ContainerBuilder');

        $container->expects($this->never())->method('findTaggedServiceIds');
        $container->expects($this->never())->method('getDefinition');
        $container->expects($this->atLeastOnce())
            ->method('hasDefinition')
            ->with('cache_warmer')
            ->will($this->returnValue(false));
        $definition->expects($this->never())->method('replaceArgument');

        $addCacheWarmerPass = new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_AddCacheWarmerPass();
        $addCacheWarmerPass->process($container);
    }

    public function testThatCacheWarmersMightBeNotDefined()
    {
        $definition = $this->getMock('Symfony_Component_DependencyInjection_Definition');
        $container = $this->getMock('Symfony_Component_DependencyInjection_ContainerBuilder');

        $container->expects($this->atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will($this->returnValue(array()));
        $container->expects($this->never())->method('getDefinition');
        $container->expects($this->atLeastOnce())
            ->method('hasDefinition')
            ->with('cache_warmer')
            ->will($this->returnValue(true));

        $definition->expects($this->never())->method('replaceArgument');

        $addCacheWarmerPass = new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_AddCacheWarmerPass();
        $addCacheWarmerPass->process($container);
    }
}
