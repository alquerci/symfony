<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Templating_Helper_RequestHelperTest extends PHPUnit_Framework_TestCase
{
    protected $request;

    protected function setUp()
    {
        $this->request = new Symfony_Component_HttpFoundation_Request();
        $this->request->initialize(array('foobar' => 'bar'));
    }

    protected function tearDown()
    {
        $this->request = null;
    }

    public function testGetParameter()
    {
        $helper = new Symfony_Bundle_FrameworkBundle_Templating_Helper_RequestHelper($this->request);

        $this->assertEquals('bar', $helper->getParameter('foobar'));
        $this->assertEquals('foo', $helper->getParameter('bar', 'foo'));

        $this->assertNull($helper->getParameter('foo'));
    }

    public function testGetLocale()
    {
        $helper = new Symfony_Bundle_FrameworkBundle_Templating_Helper_RequestHelper($this->request);

        $this->assertEquals('en', $helper->getLocale());
    }

    public function testGetName()
    {
        $helper = new Symfony_Bundle_FrameworkBundle_Templating_Helper_RequestHelper($this->request);

        $this->assertEquals('request', $helper->getName());
    }
}
