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
 * Test class for Symfony_Component_HttpFoundation_Session_Storage_NativeSessionStorage.
 *
 * @author Drak <drak@zikula.org>
 *
 * These tests require separate processes.
 *
 * @runTestsInSeparateProcesses
 */
class Symfony_Component_HttpFoundation_Tests_Session_Storage_NativeSessionStorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param array $options
     *
     * @return Symfony_Component_HttpFoundation_Session_Storage_NativeSessionStorage
     */
    protected function getStorage(array $options = array())
    {
        $storage = new Symfony_Component_HttpFoundation_Session_Storage_NativeSessionStorage($options);
        $storage->registerBag(new Symfony_Component_HttpFoundation_Session_Attribute_AttributeBag);

        return $storage;
    }

    public function testBag()
    {
        $storage = $this->getStorage();
        $bag = new Symfony_Component_HttpFoundation_Session_Flash_FlashBag();
        $storage->registerBag($bag);
        $this->assertSame($bag, $storage->getBag($bag->getName()));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegisterBagException()
    {
        $storage = $this->getStorage();
        $storage->getBag('non_existing');
    }

    public function testGetId()
    {
        $storage = $this->getStorage();
        $this->assertEquals('', $storage->getId());
        $storage->start();
        $this->assertNotEquals('', $storage->getId());
    }

    public function testRegenerate()
    {
        $storage = $this->getStorage();
        $storage->start();
        $id = $storage->getId();
        $storage->getBag('attributes')->set('lucky', 7);
        $storage->regenerate();
        $this->assertNotEquals($id, $storage->getId());
        $this->assertEquals(7, $storage->getBag('attributes')->get('lucky'));

    }

    public function testRegenerateDestroy()
    {
        $storage = $this->getStorage();
        $storage->start();
        $id = $storage->getId();
        $storage->getBag('attributes')->set('legs', 11);
        $storage->regenerate(true);
        $this->assertNotEquals($id, $storage->getId());
        $this->assertEquals(11, $storage->getBag('attributes')->get('legs'));
    }

    public function testDefaultSessionCacheLimiter()
    {
        ini_set('session.cache_limiter', 'nocache');

        $storage = new Symfony_Component_HttpFoundation_Session_Storage_NativeSessionStorage();
        $this->assertEquals('', ini_get('session.cache_limiter'));
    }

    public function testExplicitSessionCacheLimiter()
    {
        ini_set('session.cache_limiter', 'nocache');

        $storage = new Symfony_Component_HttpFoundation_Session_Storage_NativeSessionStorage(array('cache_limiter' => 'public'));
        $this->assertEquals('public', ini_get('session.cache_limiter'));
    }

    public function testCookieOptions()
    {
        $options = array(
            'cookie_lifetime' => 123456,
            'cookie_path' => '/my/cookie/path',
            'cookie_domain' => 'symfony2.example.com',
            'cookie_secure' => true,
            'cookie_httponly' => false,
        );

        if (version_compare(PHP_VERSION, '5.2.0', '<')) {
            unset($options['cookie_httponly']);
        }

        $this->getStorage($options);
        $temp = session_get_cookie_params();
        $gco = array();

        foreach ($temp as $key => $value) {
            $gco['cookie_'.$key] = $value;
        }

        $this->assertEquals($options, $gco);
    }

    public function testSetSaveHandler()
    {
        $storage = $this->getStorage();
        $storage->setSaveHandler(new StdClass());
        $this->assertInstanceOf('Symfony_Component_HttpFoundation_Session_Storage_Proxy_NativeProxy', $storage->getSaveHandler());
    }

    public function testSetSaveHandlerPHP53()
    {
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            $this->markTestSkipped('Test skipped, for PHP 5.3 only.');
        }

        $storage = $this->getStorage();
        $storage->setSaveHandler(new Symfony_Component_HttpFoundation_Session_Storage_Handler_NativeFileSessionHandler());
        $this->assertInstanceOf('Symfony_Component_HttpFoundation_Session_Storage_Proxy_NativeProxy', $storage->getSaveHandler());
    }

    public function testSetSaveHandlerPHP54()
    {
        if (version_compare(phpversion(), '5.4.0', '<')) {
            $this->markTestSkipped('Test skipped, for PHP 5.4+ only.');
        }

        $storage = $this->getStorage();
        $storage->setSaveHandler(new Symfony_Component_HttpFoundation_Session_Storage_Handler_NullSessionHandler());
        $this->assertInstanceOf('Symfony_Component_HttpFoundation_Session_Storage_Proxy_SessionHandlerProxy', $storage->getSaveHandler());
    }
}
