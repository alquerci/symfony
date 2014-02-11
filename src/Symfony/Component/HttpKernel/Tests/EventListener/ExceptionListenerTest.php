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
 * ExceptionListenerTest
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 */
class Symfony_Component_HttpKernel_Tests_EventListener_ExceptionListenerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }
    }

    public function testConstruct()
    {
        $logger = new Symfony_Component_HttpKernel_Tests_EventListener_TestLogger();
        $l = new Symfony_Component_HttpKernel_EventListener_ExceptionListener('foo', $logger);

        $this->assertSame($logger, $this->readAttribute($l, 'logger'));
        $this->assertSame('foo', $this->readAttribute($l, 'controller'));
    }

    /**
     * @dataProvider provider
     */
    public function testHandleWithoutLogger($event, $event2)
    {
        // store the current error_log, and disable it temporarily
        $errorLog = ini_set('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $l = new Symfony_Component_HttpKernel_EventListener_ExceptionListener('foo');
        $l->onKernelException($event);

        $this->assertEquals(new Symfony_Component_HttpFoundation_Response('foo'), $event->getResponse());

        try {
            $l->onKernelException($event2);
        } catch (Exception $e) {
            $this->assertSame('foo', $e->getMessage());
        }

        // restore the old error_log
        ini_set('error_log', $errorLog);
    }

    /**
     * @dataProvider provider
     */
    public function testHandleWithLogger($event, $event2)
    {
        $logger = new Symfony_Component_HttpKernel_Tests_EventListener_TestLogger();

        $l = new Symfony_Component_HttpKernel_EventListener_ExceptionListener('foo', $logger);
        $l->onKernelException($event);

        $this->assertEquals(new Symfony_Component_HttpFoundation_Response('foo'), $event->getResponse());

        try {
            $l->onKernelException($event2);
        } catch (Exception $e) {
            $this->assertSame('foo', $e->getMessage());
        }

        $this->assertEquals(3, $logger->countErrors());
        $this->assertCount(3, $logger->getLogs('critical'));
    }

    public function provider()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            return array(array(null, null));
        }

        $request = new Symfony_Component_HttpFoundation_Request();
        $exception = new Exception('foo');
        $event = new Symfony_Component_HttpKernel_Event_GetResponseForExceptionEvent(new Symfony_Component_HttpKernel_Tests_EventListener_TestKernel(), $request, 'foo', $exception);
        $event2 = new Symfony_Component_HttpKernel_Event_GetResponseForExceptionEvent(new Symfony_Component_HttpKernel_Tests_EventListener_TestKernelThatThrowsException(), $request, 'foo', $exception);

        return array(
            array($event, $event2)
        );
    }
}

class Symfony_Component_HttpKernel_Tests_EventListener_TestLogger extends Symfony_Component_HttpKernel_Tests_Logger implements Symfony_Component_HttpKernel_Log_DebugLoggerInterface
{
    public function countErrors()
    {
        return count($this->logs['critical']);
    }
}

class Symfony_Component_HttpKernel_Tests_EventListener_TestKernel implements Symfony_Component_HttpKernel_HttpKernelInterface
{
    public function handle(Symfony_Component_HttpFoundation_Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        return new Symfony_Component_HttpFoundation_Response('foo');
    }
}

class Symfony_Component_HttpKernel_Tests_EventListener_TestKernelThatThrowsException implements Symfony_Component_HttpKernel_HttpKernelInterface
{
    public function handle(Symfony_Component_HttpFoundation_Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        throw new Exception('bar');
    }
}
