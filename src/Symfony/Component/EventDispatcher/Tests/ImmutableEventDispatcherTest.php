<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_EventDispatcher_Tests_ImmutableEventDispatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $innerDispatcher;

    /**
     * @var Symfony_Component_EventDispatcher_ImmutableEventDispatcher
     */
    private $dispatcher;

    protected function setUp()
    {
        $this->innerDispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $this->dispatcher = new Symfony_Component_EventDispatcher_ImmutableEventDispatcher($this->innerDispatcher);
    }

    public function testDispatchDelegates()
    {
        $event = new Symfony_Component_EventDispatcher_Event();

        $this->innerDispatcher->expects($this->once())
            ->method('dispatch')
            ->with('event', $event)
            ->will($this->returnValue('result'));

        $this->assertSame('result', $this->dispatcher->dispatch('event', $event));
    }

    public function testGetListenersDelegates()
    {
        $this->innerDispatcher->expects($this->once())
            ->method('getListeners')
            ->with('event')
            ->will($this->returnValue('result'));

        $this->assertSame('result', $this->dispatcher->getListeners('event'));
    }

    public function testHasListenersDelegates()
    {
        $this->innerDispatcher->expects($this->once())
            ->method('hasListeners')
            ->with('event')
            ->will($this->returnValue('result'));

        $this->assertSame('result', $this->dispatcher->hasListeners('event'));
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testAddListenerDisallowed()
    {
        $this->dispatcher->addListener('event', create_function('', 'return "foo";'));
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testAddSubscriberDisallowed()
    {
        $subscriber = $this->getMock('Symfony_Component_EventDispatcher_EventSubscriberInterface');

        $this->dispatcher->addSubscriber($subscriber);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testRemoveListenerDisallowed()
    {
        $this->dispatcher->removeListener('event', create_function('', 'return "foo";'));
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testRemoveSubscriberDisallowed()
    {
        $subscriber = $this->getMock('Symfony_Component_EventDispatcher_EventSubscriberInterface');

        $this->dispatcher->removeSubscriber($subscriber);
    }
}
