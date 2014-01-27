<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Config_Tests_Loader_DelegatingLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Symfony_Component_Config_Loader_DelegatingLoader::__construct
     */
    public function testConstructor()
    {
        $loader = new Symfony_Component_Config_Loader_DelegatingLoader($resolver = new Symfony_Component_Config_Loader_LoaderResolver());
        $this->assertTrue(true, '__construct() takes a loader resolver as its first argument');
    }

    /**
     * @covers Symfony_Component_Config_Loader_DelegatingLoader::getResolver
     * @covers Symfony_Component_Config_Loader_DelegatingLoader::setResolver
     */
    public function testGetSetResolver()
    {
        $resolver = new Symfony_Component_Config_Loader_LoaderResolver();
        $loader = new Symfony_Component_Config_Loader_DelegatingLoader($resolver);
        $this->assertSame($resolver, $loader->getResolver(), '->getResolver() gets the resolver loader');
        $loader->setResolver($resolver = new Symfony_Component_Config_Loader_LoaderResolver());
        $this->assertSame($resolver, $loader->getResolver(), '->setResolver() sets the resolver loader');
    }

    /**
     * @covers Symfony_Component_Config_Loader_DelegatingLoader::supports
     */
    public function testSupports()
    {
        $loader1 = $this->getMock('Symfony_Component_Config_Loader_LoaderInterface');
        $loader1->expects($this->once())->method('supports')->will($this->returnValue(true));
        $loader = new Symfony_Component_Config_Loader_DelegatingLoader(new Symfony_Component_Config_Loader_LoaderResolver(array($loader1)));
        $this->assertTrue($loader->supports('foo.xml'), '->supports() returns true if the resource is loadable');

        $loader1 = $this->getMock('Symfony_Component_Config_Loader_LoaderInterface');
        $loader1->expects($this->once())->method('supports')->will($this->returnValue(false));
        $loader = new Symfony_Component_Config_Loader_DelegatingLoader(new Symfony_Component_Config_Loader_LoaderResolver(array($loader1)));
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns false if the resource is not loadable');
    }

    /**
     * @covers Symfony_Component_Config_Loader_DelegatingLoader::load
     */
    public function testLoad()
    {
        $loader = $this->getMock('Symfony_Component_Config_Loader_LoaderInterface');
        $loader->expects($this->once())->method('supports')->will($this->returnValue(true));
        $loader->expects($this->once())->method('load');
        $resolver = new Symfony_Component_Config_Loader_LoaderResolver(array($loader));
        $loader = new Symfony_Component_Config_Loader_DelegatingLoader($resolver);

        $loader->load('foo');
    }

    /**
     * @expectedException Symfony_Component_Config_Exception_FileLoaderLoadException
     */
    public function testLoadThrowsAnExceptionIfTheResourceCannotBeLoaded()
    {
        $loader = $this->getMock('Symfony_Component_Config_Loader_LoaderInterface');
        $loader->expects($this->once())->method('supports')->will($this->returnValue(false));
        $resolver = new Symfony_Component_Config_Loader_LoaderResolver(array($loader));
        $loader = new Symfony_Component_Config_Loader_DelegatingLoader($resolver);

        $loader->load('foo');
    }
}
