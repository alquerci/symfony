<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_DependencyInjection_Compiler_RegisterKernelListenersPassTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests that event subscribers not implementing EventSubscriberInterface
     * trigger an exception.
     *
     * @expectedException InvalidArgumentException
     */
    public function testEventSubscriberWithoutInterface()
    {
        // one service, not implementing any interface
        $services = array(
            'my_event_subscriber' => array(0 => array()),
        );

        $definition = $this->getMock('Symfony_Component_DependencyInjection_Definition');
        $definition->expects($this->atLeastOnce())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $builder = $this->getMock('Symfony_Component_DependencyInjection_ContainerBuilder');
        $builder->expects($this->any())
            ->method('hasDefinition')
            ->will($this->returnValue(true));

        // We don't test kernel.event_listener here
        $builder->expects($this->atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will($this->onConsecutiveCalls(array(), $services));

        $builder->expects($this->atLeastOnce())
            ->method('getDefinition')
            ->will($this->returnValue($definition));

        $registerListenersPass = new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_RegisterKernelListenersPass();
        $registerListenersPass->process($builder);
    }

    public function testValidEventSubscriber()
    {
        $services = array(
            'my_event_subscriber' => array(0 => array()),
        );

        $definition = $this->getMock('Symfony_Component_DependencyInjection_Definition');
        $definition->expects($this->atLeastOnce())
            ->method('getClass')
            ->will($this->returnValue('Symfony_Bundle_FrameworkBundle_Tests_DependencyInjection_Compiler_SubscriberService'));

        $builder = $this->getMock('Symfony_Component_DependencyInjection_ContainerBuilder');
        $builder->expects($this->any())
            ->method('hasDefinition')
            ->will($this->returnValue(true));

        // We don't test kernel.event_listener here
        $builder->expects($this->atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will($this->onConsecutiveCalls(array(), $services));

        $builder->expects($this->atLeastOnce())
            ->method('getDefinition')
            ->will($this->returnValue($definition));

        $registerListenersPass = new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_RegisterKernelListenersPass();
        $registerListenersPass->process($builder);
    }
}

class Symfony_Bundle_FrameworkBundle_Tests_DependencyInjection_Compiler_SubscriberService implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    public static function getSubscribedEvents() {}
}
