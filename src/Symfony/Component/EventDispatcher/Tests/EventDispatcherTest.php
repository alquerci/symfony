<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_EventDispatcher_Tests_EventDispatcherTest extends PHPUnit_Framework_TestCase
{
    /* Some pseudo events */
    const preFoo = 'pre.foo';
    const postFoo = 'post.foo';
    const preBar = 'pre.bar';
    const postBar = 'post.bar';

    private $dispatcher;

    private $listener;

    protected function setUp()
    {
        $this->dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $this->listener = new Symfony_Component_EventDispatcher_Tests_TestEventListener();
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->listener = null;
    }

    public function testInitialState()
    {
        $this->assertEquals(array(), $this->dispatcher->getListeners());
        $this->assertFalse($this->dispatcher->hasListeners(self::preFoo));
        $this->assertFalse($this->dispatcher->hasListeners(self::postFoo));
    }

    public function testAddListener()
    {
        $this->dispatcher->addListener('pre.foo', array($this->listener, 'preFoo'));
        $this->dispatcher->addListener('post.foo', array($this->listener, 'postFoo'));
        $this->assertTrue($this->dispatcher->hasListeners(self::preFoo));
        $this->assertTrue($this->dispatcher->hasListeners(self::postFoo));
        $this->assertCount(1, $this->dispatcher->getListeners(self::preFoo));
        $this->assertCount(1, $this->dispatcher->getListeners(self::postFoo));
        $this->assertCount(2, $this->dispatcher->getListeners());
    }

    public function testGetListenersSortsByPriority()
    {
        $listener1 = new Symfony_Component_EventDispatcher_Tests_TestEventListener();
        $listener2 = new Symfony_Component_EventDispatcher_Tests_TestEventListener();
        $listener3 = new Symfony_Component_EventDispatcher_Tests_TestEventListener();
        $listener1->name = '1';
        $listener2->name = '2';
        $listener3->name = '3';

        $this->dispatcher->addListener('pre.foo', array($listener1, 'preFoo'), -10);
        $this->dispatcher->addListener('pre.foo', array($listener2, 'preFoo'), 10);
        $this->dispatcher->addListener('pre.foo', array($listener3, 'preFoo'));

        $expected = array(
            array($listener2, 'preFoo'),
            array($listener3, 'preFoo'),
            array($listener1, 'preFoo'),
        );

        $this->assertSame($expected, $this->dispatcher->getListeners('pre.foo'));
    }

    public function testGetAllListenersSortsByPriority()
    {
        $listener1 = new Symfony_Component_EventDispatcher_Tests_TestEventListener();
        $listener2 = new Symfony_Component_EventDispatcher_Tests_TestEventListener();
        $listener3 = new Symfony_Component_EventDispatcher_Tests_TestEventListener();
        $listener4 = new Symfony_Component_EventDispatcher_Tests_TestEventListener();
        $listener5 = new Symfony_Component_EventDispatcher_Tests_TestEventListener();
        $listener6 = new Symfony_Component_EventDispatcher_Tests_TestEventListener();

        $this->dispatcher->addListener('pre.foo', $listener1, -10);
        $this->dispatcher->addListener('pre.foo', $listener2);
        $this->dispatcher->addListener('pre.foo', $listener3, 10);
        $this->dispatcher->addListener('post.foo', $listener4, -10);
        $this->dispatcher->addListener('post.foo', $listener5);
        $this->dispatcher->addListener('post.foo', $listener6, 10);

        $expected = array(
            'pre.foo'  => array($listener3, $listener2, $listener1),
            'post.foo' => array($listener6, $listener5, $listener4),
        );

        $this->assertSame($expected, $this->dispatcher->getListeners());
    }

    public function testDispatch()
    {
        $this->dispatcher->addListener('pre.foo', array($this->listener, 'preFoo'));
        $this->dispatcher->addListener('post.foo', array($this->listener, 'postFoo'));
        $this->dispatcher->dispatch(self::preFoo);
        $this->assertTrue($this->listener->preFooInvoked);
        $this->assertFalse($this->listener->postFooInvoked);
        $this->assertInstanceOf('Symfony_Component_EventDispatcher_Event', $this->dispatcher->dispatch('noevent'));
        $this->assertInstanceOf('Symfony_Component_EventDispatcher_Event', $this->dispatcher->dispatch(self::preFoo));
        $event = new Symfony_Component_EventDispatcher_Event();
        $return = $this->dispatcher->dispatch(self::preFoo, $event);
        $this->assertEquals('pre.foo', $event->getName());
        $this->assertSame($event, $return);
    }

    public function testDispatchForClosure()
    {
        $invoked = 0;
        $listener = array(new Symfony_Component_EventDispatcher_Tests_Closure($invoked), 'addOne');
        $this->dispatcher->addListener('pre.foo', $listener);
        $this->dispatcher->addListener('post.foo', $listener);
        $this->dispatcher->dispatch(self::preFoo);
        $this->assertEquals(1, $invoked);
    }

    public function testStopEventPropagation()
    {
        $otherListener = new Symfony_Component_EventDispatcher_Tests_TestEventListener();

        // postFoo() stops the propagation, so only one listener should
        // be executed
        // Manually set priority to enforce $this->listener to be called first
        $this->dispatcher->addListener('post.foo', array($this->listener, 'postFoo'), 10);
        $this->dispatcher->addListener('post.foo', array($otherListener, 'preFoo'));
        $this->dispatcher->dispatch(self::postFoo);
        $this->assertTrue($this->listener->postFooInvoked);
        $this->assertFalse($otherListener->postFooInvoked);
    }

    public function testDispatchByPriority()
    {
        $invoked = array();
        $listener1 = array(new Symfony_Component_EventDispatcher_Tests_Closure($invoked, '1'), 'append');
        $listener2 = array(new Symfony_Component_EventDispatcher_Tests_Closure($invoked, '2'), 'append');
        $listener3 = array(new Symfony_Component_EventDispatcher_Tests_Closure($invoked, '3'), 'append');
        $this->dispatcher->addListener('pre.foo', $listener1, -10);
        $this->dispatcher->addListener('pre.foo', $listener2);
        $this->dispatcher->addListener('pre.foo', $listener3, 10);
        $this->dispatcher->dispatch(self::preFoo);
        $this->assertEquals(array('3', '2', '1'), $invoked);
    }

    public function testRemoveListener()
    {
        $this->dispatcher->addListener('pre.bar', $this->listener);
        $this->assertTrue($this->dispatcher->hasListeners(self::preBar));
        $this->dispatcher->removeListener('pre.bar', $this->listener);
        $this->assertFalse($this->dispatcher->hasListeners(self::preBar));
        $this->dispatcher->removeListener('notExists', $this->listener);
    }

    public function testAddSubscriber()
    {
        $eventSubscriber = new Symfony_Component_EventDispatcher_Tests_TestEventSubscriber();
        $this->dispatcher->addSubscriber($eventSubscriber);
        $this->assertTrue($this->dispatcher->hasListeners(self::preFoo));
        $this->assertTrue($this->dispatcher->hasListeners(self::postFoo));
    }

    public function testAddSubscriberWithPriorities()
    {
        $eventSubscriber = new Symfony_Component_EventDispatcher_Tests_TestEventSubscriber();
        $this->dispatcher->addSubscriber($eventSubscriber);

        $eventSubscriber = new Symfony_Component_EventDispatcher_Tests_TestEventSubscriberWithPriorities();
        $this->dispatcher->addSubscriber($eventSubscriber);

        $listeners = $this->dispatcher->getListeners('pre.foo');
        $this->assertTrue($this->dispatcher->hasListeners(self::preFoo));
        $this->assertCount(2, $listeners);
        $this->assertInstanceOf('Symfony_Component_EventDispatcher_Tests_TestEventSubscriberWithPriorities', $listeners[0][0]);
    }

    public function testAddSubscriberWithMultipleListeners()
    {
        $eventSubscriber = new Symfony_Component_EventDispatcher_Tests_TestEventSubscriberWithMultipleListeners();
        $this->dispatcher->addSubscriber($eventSubscriber);

        $listeners = $this->dispatcher->getListeners('pre.foo');
        $this->assertTrue($this->dispatcher->hasListeners(self::preFoo));
        $this->assertCount(2, $listeners);
        $this->assertEquals('preFoo2', $listeners[0][1]);
    }

    public function testRemoveSubscriber()
    {
        $eventSubscriber = new Symfony_Component_EventDispatcher_Tests_TestEventSubscriber();
        $this->dispatcher->addSubscriber($eventSubscriber);
        $this->assertTrue($this->dispatcher->hasListeners(self::preFoo));
        $this->assertTrue($this->dispatcher->hasListeners(self::postFoo));
        $this->dispatcher->removeSubscriber($eventSubscriber);
        $this->assertFalse($this->dispatcher->hasListeners(self::preFoo));
        $this->assertFalse($this->dispatcher->hasListeners(self::postFoo));
    }

    public function testRemoveSubscriberWithPriorities()
    {
        $eventSubscriber = new Symfony_Component_EventDispatcher_Tests_TestEventSubscriberWithPriorities();
        $this->dispatcher->addSubscriber($eventSubscriber);
        $this->assertTrue($this->dispatcher->hasListeners(self::preFoo));
        $this->dispatcher->removeSubscriber($eventSubscriber);
        $this->assertFalse($this->dispatcher->hasListeners(self::preFoo));
    }

    public function testRemoveSubscriberWithMultipleListeners()
    {
        $eventSubscriber = new Symfony_Component_EventDispatcher_Tests_TestEventSubscriberWithMultipleListeners();
        $this->dispatcher->addSubscriber($eventSubscriber);
        $this->assertTrue($this->dispatcher->hasListeners(self::preFoo));
        $this->assertCount(2, $this->dispatcher->getListeners(self::preFoo));
        $this->dispatcher->removeSubscriber($eventSubscriber);
        $this->assertFalse($this->dispatcher->hasListeners(self::preFoo));
    }

    public function testEventReceivesTheDispatcherInstance()
    {
        $test = $this;
        $this->dispatcher->addListener('test', array(new Symfony_Component_EventDispatcher_Tests_Closure($dispatcher), 'affectDispatcher'));
        $this->dispatcher->dispatch('test');
        $this->assertSame($this->dispatcher, $dispatcher);
    }

    /**
     * @see https://bugs.php.net/bug.php?id=62976
     *
     * This bug affects:
     *  - The PHP 5.3 branch for versions < 5.3.18
     *  - The PHP 5.4 branch for versions < 5.4.8
     *  - The PHP 5.5 branch is not affected
     */
    public function testWorkaroundForPhpBug62976()
    {
        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $dispatcher->addListener('bug.62976', new Symfony_Component_EventDispatcher_Tests_CallableClass());
        $dispatcher->removeListener('bug.62976', create_function('', ''));
        $this->assertTrue($dispatcher->hasListeners('bug.62976'));
    }
}

class Symfony_Component_EventDispatcher_Tests_CallableClass
{
    public function __invoke()
    {
    }
}

class Symfony_Component_EventDispatcher_Tests_TestEventListener
{
    public $preFooInvoked = false;
    public $postFooInvoked = false;

    /* Listener methods */

    public function preFoo(Symfony_Component_EventDispatcher_Event $e)
    {
        $this->preFooInvoked = true;
    }

    public function postFoo(Symfony_Component_EventDispatcher_Event $e)
    {
        $this->postFooInvoked = true;

        $e->stopPropagation();
    }
}

class Symfony_Component_EventDispatcher_Tests_TestEventSubscriber implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array('pre.foo' => 'preFoo', 'post.foo' => 'postFoo');
    }
}

class Symfony_Component_EventDispatcher_Tests_TestEventSubscriberWithPriorities implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'pre.foo' => array('preFoo', 10),
            'post.foo' => array('postFoo'),
            );
    }
}

class Symfony_Component_EventDispatcher_Tests_TestEventSubscriberWithMultipleListeners implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array('pre.foo' => array(
            array('preFoo1'),
            array('preFoo2', 10)
        ));
    }
}

class Symfony_Component_EventDispatcher_Tests_Closure
{
    private $value;
    private $id;

    public function __construct(&$value, $id = null)
    {
        $this->value = &$value;
        $this->id = $id;
    }

    public function addOne()
    {
        ++$this->value;
    }

    public function append()
    {
        $this->value[] = $this->id;
    }

    public function affectDispatcher($event)
    {
        $this->value = $event->getDispatcher();
    }
}
