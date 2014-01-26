<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_DependencyInjection_Compiler_TranslatorPassTest extends PHPUnit_Framework_TestCase
{
    public function testValidCollector()
    {
        $definition = $this->getMock('Symfony_Component_DependencyInjection_Definition');
        $definition->expects($this->at(0))
            ->method('addMethodCall')
            ->with('addLoader', array('xliff', new Symfony_Component_DependencyInjection_Reference('xliff')));
        $definition->expects($this->at(1))
            ->method('addMethodCall')
            ->with('addLoader', array('xlf', new Symfony_Component_DependencyInjection_Reference('xliff')));

        $container = $this->getMock('Symfony_Component_DependencyInjection_ContainerBuilder');
        $container->expects($this->any())
            ->method('hasDefinition')
            ->will($this->returnValue(true));
        $container->expects($this->once())
            ->method('getDefinition')
            ->will($this->returnValue($definition));
        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->will($this->returnValue(array('xliff' => array(array('alias' => 'xliff', 'legacy-alias' => 'xlf')))));
        $container->expects($this->once())
            ->method('findDefinition')
            ->will($this->returnValue($this->getMock('Symfony_Component_DependencyInjection_Definition')));
        ;

        $pass = new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_TranslatorPass();
        $pass->process($container);
    }
}
