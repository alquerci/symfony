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
 * Test class for Symfony_Component_HttpFoundation_Session_Storage_Handler_NullSessionHandler.
 *
 * @author Drak <drak@zikula.org>
 *
 * @runTestsInSeparateProcesses
 */
class Symfony_Component_HttpFoundation_Tests_Session_Storage_Handler_NullSessionStorageTest extends PHPUnit_Framework_TestCase
{
    public function testSaveHandlers()
    {
        $storage = $this->getStorage();
        $this->assertEquals('user', ini_get('session.save_handler'));
    }

    public function testSession()
    {
        session_id('nullsessionstorage');
        $storage = $this->getStorage();
        $session = new Symfony_Component_HttpFoundation_Session_Session($storage);
        $this->assertNull($session->get('something'));
        $session->set('something', 'unique');
        $this->assertEquals('unique', $session->get('something'));
    }

    public function testNothingIsPersisted()
    {
        session_id('nullsessionstorage');
        $storage = $this->getStorage();
        $session = new Symfony_Component_HttpFoundation_Session_Session($storage);
        $session->start();
        $this->assertEquals('nullsessionstorage', $session->getId());
        $this->assertNull($session->get('something'));
    }

    public function getStorage()
    {
        return new Symfony_Component_HttpFoundation_Session_Storage_NativeSessionStorage(array(), new Symfony_Component_HttpFoundation_Session_Storage_Handler_NullSessionHandler());
    }
}
