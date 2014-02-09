<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_Extension_ExtensionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getResolvedEnabledFixtures
     */
    public function testIsConfigEnabledReturnsTheResolvedValue($enabled)
    {
        $pb = $this->getMockBuilder('Symfony_Component_DependencyInjection_ParameterBag_ParameterBag')
            ->setMethods(array('resolveValue'))
            ->getMock()
        ;

        $container = $this->getMockBuilder('Symfony_Component_DependencyInjection_ContainerBuilder')
            ->setMethods(array('getParameterBag'))
            ->getMock()
        ;

        $pb->expects($this->once())
            ->method('resolveValue')
            ->with($this->equalTo($enabled))
            ->will($this->returnValue($enabled))
        ;

        $container->expects($this->once())
            ->method('getParameterBag')
            ->will($this->returnValue($pb))
        ;

        $extension = new Symfony_Component_DependencyInjection_Tests_Extension_Extension();
        $extension->isConfigEnabled($container, array('enabled' => $enabled));
    }

    public function getResolvedEnabledFixtures()
    {
        return array(
            array(true),
            array(false)
        );
    }

    /**
     * @expectedException Symfony_Component_DependencyInjection_Exception_InvalidArgumentException
     * @expectedExceptionMessage The config array has no 'enabled' key.
     */
    public function testIsConfigEnabledOnNonEnableableConfig()
    {
        $container = $this->getMockBuilder('Symfony_Component_DependencyInjection_ContainerBuilder')
            ->getMock()
        ;

        $extension = new Symfony_Component_DependencyInjection_Tests_Extension_Extension();
        $extension->isConfigEnabled($container, array());
    }
}

class Symfony_Component_DependencyInjection_Tests_Extension_Extension extends Symfony_Component_DependencyInjection_Extension_Extension
{
    public function load(array $config, Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
    }

    public function isConfigEnabled(Symfony_Component_DependencyInjection_ContainerBuilder $container, array $config)
    {
        return parent::isConfigEnabled($container, $config);
    }
}
