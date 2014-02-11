<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED', 8192);
}

if (!defined('E_USER_DEPRECATED')) {
    define('E_USER_DEPRECATED', 16384);
}

/**
 * ErrorHandlerTest
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 */
class Symfony_Component_HttpKernel_Tests_Debug_ErrorHandlerTest extends PHPUnit_Framework_TestCase
{
    private $backupDisplayErrors;
    private $backupErrorHandler;

    protected function setUp()
    {
        $this->backupDisplayErrors = ini_get('display_errors');

        $this->backupErrorHandler = set_error_handler(create_function('', ''));
        restore_error_handler();
    }

    protected function tearDown()
    {
        ini_set('display_errors', $this->backupDisplayErrors);

        if (null !== $this->backupErrorHandler) {
            set_error_handler($this->backupErrorHandler);
        }
    }

    public function testConstruct()
    {
        $handler = Symfony_Component_HttpKernel_Debug_ErrorHandler::register(3);

        $this->assertEquals(3, $this->readAttribute($handler, 'level'));

        restore_error_handler();
    }

    public function testHandle()
    {
        $handler = Symfony_Component_HttpKernel_Debug_ErrorHandler::register(0);
        $this->assertFalse($handler->handle(0, 'foo', 'foo.php', 12, 'foo'));

        restore_error_handler();

        $handler = Symfony_Component_HttpKernel_Debug_ErrorHandler::register(3);
        $this->assertFalse($handler->handle(4, 'foo', 'foo.php', 12, 'foo'));

        restore_error_handler();

        $handler = Symfony_Component_HttpKernel_Debug_ErrorHandler::register(3);
        try {
            $handler->handle(111, 'foo', 'foo.php', 12, 'foo');
        } catch (ErrorException $e) {
            $this->assertSame('111: foo in foo.php line 12', $e->getMessage());
            $this->assertSame(111, $e->getSeverity());
            $this->assertSame('foo.php', $e->getFile());
            $this->assertSame(12, $e->getLine());
        }

        restore_error_handler();

        $handler = Symfony_Component_HttpKernel_Debug_ErrorHandler::register(E_USER_DEPRECATED);
        $this->assertTrue($handler->handle(E_USER_DEPRECATED, 'foo', 'foo.php', 12, 'foo'));

        restore_error_handler();

        $handler = Symfony_Component_HttpKernel_Debug_ErrorHandler::register(E_DEPRECATED);
        $this->assertTrue($handler->handle(E_DEPRECATED, 'foo', 'foo.php', 12, 'foo'));

        restore_error_handler();

        $logger = $this->getMock('Psr_Log_LoggerInterface');

        $that = $this;
        $warnArgCheck = array(new Symfony_Component_HttpKernel_Tests_Debug_ErrorHandlerTestClosure($this), '__invoke');

        $logger
            ->expects($this->once())
            ->method('warning')
            ->will($this->returnCallback($warnArgCheck))
        ;

        $handler = Symfony_Component_HttpKernel_Debug_ErrorHandler::register(E_USER_DEPRECATED);
        $handler->setLogger($logger);
        $handler->handle(E_USER_DEPRECATED, 'foo', 'foo.php', 12, 'foo');

        restore_error_handler();
    }
}

class Symfony_Component_HttpKernel_Tests_Debug_ErrorHandlerTestClosure
{
    private $that;

    public function __construct(PHPUnit_Framework_TestCase $that)
    {
        $this->that = $that;
    }

    public function __invoke($message, $context)
    {
        $this->that->assertEquals('foo', $message);
        $this->that->assertArrayHasKey('type', $context);
        $this->that->assertEquals($context['type'], Symfony_Component_HttpKernel_Debug_ErrorHandler::TYPE_DEPRECATION);
        $this->that->assertArrayHasKey('stack', $context);
        $this->that->assertInternalType('array', $context['stack']);
    }
}
