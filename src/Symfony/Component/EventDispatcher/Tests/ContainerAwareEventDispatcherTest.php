<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_EventDispatcher_Tests_ContainerAwareEventDispatcherTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_DependencyInjection_Container')) {
            $this->markTestSkipped('The "DependencyInjection" component is not available');
        }
    }

    public function testAddAListenerService()
    {
        $event = new Symfony_Component_EventDispatcher_Event();

        $service = $this->getMock('Symfony_Component_EventDispatcher_Tests_Service');

        $service
            ->expects($this->once())
            ->method('onEvent')
            ->with($event)
        ;

        $container = new Symfony_Component_DependencyInjection_Container();
        $container->set('service.listener', $service);

        $dispatcher = new Symfony_Component_EventDispatcher_ContainerAwareEventDispatcher($container);
        $dispatcher->addListenerService('onEvent', array('service.listener', 'onEvent'));

        $dispatcher->dispatch('onEvent', $event);
    }

    public function testAddASubscriberService()
    {
        $event = new Symfony_Component_EventDispatcher_Event();

        $service = $this->getMock('Symfony_Component_EventDispatcher_Tests_SubscriberService');

        $service
            ->expects($this->once())
            ->method('onEvent')
            ->with($event)
        ;

        $container = new Symfony_Component_DependencyInjection_Container();
        $container->set('service.subscriber', $service);

        $dispatcher = new Symfony_Component_EventDispatcher_ContainerAwareEventDispatcher($container);
        $dispatcher->addSubscriberService('service.subscriber', 'Symfony_Component_EventDispatcher_Tests_SubscriberService');

        $dispatcher->dispatch('onEvent', $event);
    }

    public function testPreventDuplicateListenerService()
    {
        $event = new Symfony_Component_EventDispatcher_Event();

        $service = $this->getMock('Symfony_Component_EventDispatcher_Tests_Service');

        $service
            ->expects($this->once())
            ->method('onEvent')
            ->with($event)
        ;

        $container = new Symfony_Component_DependencyInjection_Container();
        $container->set('service.listener', $service);

        $dispatcher = new Symfony_Component_EventDispatcher_ContainerAwareEventDispatcher($container);
        $dispatcher->addListenerService('onEvent', array('service.listener', 'onEvent'), 5);
        $dispatcher->addListenerService('onEvent', array('service.listener', 'onEvent'), 10);

        $dispatcher->dispatch('onEvent', $event);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testTriggerAListenerServiceOutOfScope()
    {
        $service = $this->getMock('Symfony_Component_EventDispatcher_Tests_Service');

        $scope = new Symfony_Component_DependencyInjection_Scope('scope');
        $container = new Symfony_Component_DependencyInjection_Container();
        $container->addScope($scope);
        $container->enterScope('scope');

        $container->set('service.listener', $service, 'scope');

        $dispatcher = new Symfony_Component_EventDispatcher_ContainerAwareEventDispatcher($container);
        $dispatcher->addListenerService('onEvent', array('service.listener', 'onEvent'));

        $container->leaveScope('scope');
        $dispatcher->dispatch('onEvent');
    }

    public function testReEnteringAScope()
    {
        $event = new Symfony_Component_EventDispatcher_Event();

        $service1 = $this->getMock('Symfony_Component_EventDispatcher_Tests_Service');

        $service1
            ->expects($this->exactly(2))
            ->method('onEvent')
            ->with($event)
        ;

        $scope = new Symfony_Component_DependencyInjection_Scope('scope');
        $container = new Symfony_Component_DependencyInjection_Container();
        $container->addScope($scope);
        $container->enterScope('scope');

        $container->set('service.listener', $service1, 'scope');

        $dispatcher = new Symfony_Component_EventDispatcher_ContainerAwareEventDispatcher($container);
        $dispatcher->addListenerService('onEvent', array('service.listener', 'onEvent'));
        $dispatcher->dispatch('onEvent', $event);

        $service2 = $this->getMock('Symfony_Component_EventDispatcher_Tests_Service');

        $service2
            ->expects($this->once())
            ->method('onEvent')
            ->with($event)
        ;

        $container->enterScope('scope');
        $container->set('service.listener', $service2, 'scope');

        $dispatcher->dispatch('onEvent', $event);

        $container->leaveScope('scope');

        $dispatcher->dispatch('onEvent');
    }

    public function testHasListenersOnLazyLoad()
    {
        $event = new Symfony_Component_EventDispatcher_Event();

        $service = $this->getMock('Symfony_Component_EventDispatcher_Tests_Service');

        $container = new Symfony_Component_DependencyInjection_Container();
        $container->set('service.listener', $service);

        $dispatcher = new Symfony_Component_EventDispatcher_ContainerAwareEventDispatcher($container);
        $dispatcher->addListenerService('onEvent', array('service.listener', 'onEvent'));

        $event->setDispatcher($dispatcher);
        $event->setName('onEvent');

        $service
            ->expects($this->once())
            ->method('onEvent')
            ->with($event)
        ;

        $this->assertTrue($dispatcher->hasListeners());

        if ($dispatcher->hasListeners('onEvent')) {
            $dispatcher->dispatch('onEvent');
        }
    }

    public function testGetListenersOnLazyLoad()
    {
        $event = new Symfony_Component_EventDispatcher_Event();

        $service = $this->getMock('Symfony_Component_EventDispatcher_Tests_Service');

        $container = new Symfony_Component_DependencyInjection_Container();
        $container->set('service.listener', $service);

        $dispatcher = new Symfony_Component_EventDispatcher_ContainerAwareEventDispatcher($container);
        $dispatcher->addListenerService('onEvent', array('service.listener', 'onEvent'));

        $listeners = $dispatcher->getListeners();

        $this->assertTrue(isset($listeners['onEvent']));

        $this->assertCount(1, $dispatcher->getListeners('onEvent'));
    }

    public function testRemoveAfterDispatch()
    {
        $event = new Symfony_Component_EventDispatcher_Event();

        $service = $this->getMock('Symfony_Component_EventDispatcher_Tests_Service');

        $container = new Symfony_Component_DependencyInjection_Container();
        $container->set('service.listener', $service);

        $dispatcher = new Symfony_Component_EventDispatcher_ContainerAwareEventDispatcher($container);
        $dispatcher->addListenerService('onEvent', array('service.listener', 'onEvent'));

        $dispatcher->dispatch('onEvent', new Symfony_Component_EventDispatcher_Event());
        $dispatcher->removeListener('onEvent', array($container->get('service.listener'), 'onEvent'));
        $this->assertFalse($dispatcher->hasListeners('onEvent'));
    }

    public function testRemoveBeforeDispatch()
    {
        $event = new Symfony_Component_EventDispatcher_Event();

        $service = $this->getMock('Symfony_Component_EventDispatcher_Tests_Service');

        $container = new Symfony_Component_DependencyInjection_Container();
        $container->set('service.listener', $service);

        $dispatcher = new Symfony_Component_EventDispatcher_ContainerAwareEventDispatcher($container);
        $dispatcher->addListenerService('onEvent', array('service.listener', 'onEvent'));

        $dispatcher->removeListener('onEvent', array($container->get('service.listener'), 'onEvent'));
        $this->assertFalse($dispatcher->hasListeners('onEvent'));
    }
}

class Symfony_Component_EventDispatcher_Tests_Service
{
    public function onEvent(Symfony_Component_EventDispatcher_Event $e)
    {
    }
}

class Symfony_Component_EventDispatcher_Tests_SubscriberService implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'onEvent' => 'onEvent',
            'onEvent' => array('onEvent', 10),
            'onEvent' => array('onEvent'),
        );
    }

    public function onEvent(Symfony_Component_EventDispatcher_Event $e)
    {
    }
}
