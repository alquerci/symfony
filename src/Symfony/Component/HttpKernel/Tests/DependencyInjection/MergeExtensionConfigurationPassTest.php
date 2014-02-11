<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_MergeExtensionConfigurationPassTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_DependencyInjection_Container')) {
            $this->markTestSkipped('The "DependencyInjection" component is not available');
        }

        if (!class_exists('Symfony_Component_Config_FileLocator')) {
            $this->markTestSkipped('The "Config" component is not available');
        }
    }

    public function testAutoloadMainExtension()
    {
        $container = $this->getMock('Symfony_Component_DependencyInjection_ContainerBuilder');
        $params = $this->getMock('Symfony_Component_DependencyInjection_ParameterBag_ParameterBag');

        $container->expects($this->at(0))
            ->method('getExtensionConfig')
            ->with('loaded')
            ->will($this->returnValue(array(array())));
        $container->expects($this->at(1))
            ->method('getExtensionConfig')
            ->with('notloaded')
            ->will($this->returnValue(array()));
        $container->expects($this->once())
            ->method('loadFromExtension')
            ->with('notloaded', array());

        $container->expects($this->any())
            ->method('getParameterBag')
            ->will($this->returnValue($params));
        $params->expects($this->any())
            ->method('all')
            ->will($this->returnValue(array()));
        $container->expects($this->any())
            ->method('getDefinitions')
            ->will($this->returnValue(array()));
        $container->expects($this->any())
            ->method('getAliases')
            ->will($this->returnValue(array()));
        $container->expects($this->any())
            ->method('getExtensions')
            ->will($this->returnValue(array()));

        $configPass = new Symfony_Component_HttpKernel_DependencyInjection_MergeExtensionConfigurationPass(array('loaded', 'notloaded'));
        $configPass->process($container);
    }
}
