<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Templating_Tests_Helper_CoreAssetsHelperTest extends PHPUnit_Framework_TestCase
{
    protected $package;

    protected function setUp()
    {
        $this->package = $this->getMock('Symfony_Component_Templating_Asset_PackageInterface');
    }

    protected function tearDown()
    {
        $this->package = null;
    }

    public function testAddGetPackage()
    {
        $helper = new Symfony_Component_Templating_Helper_CoreAssetsHelper($this->package);

        $helper->addPackage('foo', $this->package);

        $this->assertSame($this->package, $helper->getPackage('foo'));
    }

    public function testGetNonexistingPackage()
    {
        $helper = new Symfony_Component_Templating_Helper_CoreAssetsHelper($this->package);

        $this->setExpectedException('InvalidArgumentException');

        $helper->getPackage('foo');
    }

    public function testGetHelperName()
    {
        $helper = new Symfony_Component_Templating_Helper_CoreAssetsHelper($this->package);

        $this->assertEquals('assets', $helper->getName());
    }
}
