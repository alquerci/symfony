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
 * Test class for Symfony_Component_HttpFoundation_Session_Storage_Proxy_NativeProxy.
 *
 * @author Drak <drak@zikula.org>
 */
class Symfony_Component_HttpFoundation_Tests_Session_Storage_Proxy_NativeProxyTest extends PHPUnit_Framework_TestCase
{
    public function testIsWrapper()
    {
        $proxy = new Symfony_Component_HttpFoundation_Session_Storage_Proxy_NativeProxy();
        $this->assertFalse($proxy->isWrapper());
    }

    public function testGetSaveHandlerName()
    {
        $name = ini_get('session.save_handler');
        $proxy = new Symfony_Component_HttpFoundation_Session_Storage_Proxy_NativeProxy();
        $this->assertEquals($name, $proxy->getSaveHandlerName());
    }
}
