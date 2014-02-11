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
 * Test class for Symfony_Component_HttpFoundation_Session_Storage_Handler_NativeSessionHandler.
 *
 * @author Drak <drak@zikula.org>
 *
 * @runTestsInSeparateProcesses
 */
class Symfony_Component_HttpFoundation_Tests_Session_Storage_Handler_NativeSessionHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $handler = new Symfony_Component_HttpFoundation_Session_Storage_Handler_NativeSessionHandler();

        // note for PHPUnit optimisers - the use of assertTrue/False
        // here is deliberate since the tests do not require the classes to exist - drak
        if (version_compare(phpversion(), '5.4.0', '<')) {
            $this->assertFalse($handler instanceof SessionHandler);
            $this->assertTrue($handler instanceof Symfony_Component_HttpFoundation_Session_Storage_Handler_NativeSessionHandler);
        } else {
            $this->assertTrue($handler instanceof SessionHandler);
            $this->assertTrue($handler instanceof Symfony_Component_HttpFoundation_Session_Storage_Handler_NativeSessionHandler);
        }
    }
}
