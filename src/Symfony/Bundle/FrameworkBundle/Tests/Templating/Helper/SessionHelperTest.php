<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Templating_Helper_SessionHelperTest extends PHPUnit_Framework_TestCase
{
    protected $request;

    protected function setUp()
    {
        $this->request = new Symfony_Component_HttpFoundation_Request();

        $session = new Symfony_Component_HttpFoundation_Session_Session(new Symfony_Component_HttpFoundation_Session_Storage_MockArraySessionStorage());
        $session->set('foobar', 'bar');
        $session->getFlashBag()->set('notice', 'bar');

        $this->request->setSession($session);
    }

    protected function tearDown()
    {
        $this->request = null;
    }

    public function testFlash()
    {
        $helper = new Symfony_Bundle_FrameworkBundle_Templating_Helper_SessionHelper($this->request);

        $this->assertTrue($helper->hasFlash('notice'));

        $this->assertEquals(array('bar'), $helper->getFlash('notice'));
    }

    public function testGetFlashes()
    {
        $helper = new Symfony_Bundle_FrameworkBundle_Templating_Helper_SessionHelper($this->request);
        $this->assertEquals(array('notice' => array('bar')), $helper->getFlashes());
    }

    public function testGet()
    {
        $helper = new Symfony_Bundle_FrameworkBundle_Templating_Helper_SessionHelper($this->request);

        $this->assertEquals('bar', $helper->get('foobar'));
        $this->assertEquals('foo', $helper->get('bar', 'foo'));

        $this->assertNull($helper->get('foo'));
    }

    public function testGetName()
    {
        $helper = new Symfony_Bundle_FrameworkBundle_Templating_Helper_SessionHelper($this->request);

        $this->assertEquals('session', $helper->getName());
    }
}
