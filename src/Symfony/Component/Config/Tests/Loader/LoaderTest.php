<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Config_Tests_Loader_LoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Symfony_Component_Config_Loader_Loader::getResolver
     * @covers Symfony_Component_Config_Loader_Loader::setResolver
     */
    public function testGetSetResolver()
    {
        $resolver = new Symfony_Component_Config_Loader_LoaderResolver();
        $loader = new Symfony_Component_Config_Tests_Loader_ProjectLoader1();
        $loader->setResolver($resolver);
        $this->assertSame($resolver, $loader->getResolver(), '->setResolver() sets the resolver loader');
    }

    /**
     * @covers Symfony_Component_Config_Loader_Loader::resolve
     */
    public function testResolve()
    {
        $loader1 = $this->getMock('Symfony_Component_Config_Loader_LoaderInterface');
        $loader1->expects($this->once())->method('supports')->will($this->returnValue(true));
        $resolver = new Symfony_Component_Config_Loader_LoaderResolver(array($loader1));
        $loader = new Symfony_Component_Config_Tests_Loader_ProjectLoader1();
        $loader->setResolver($resolver);

        $this->assertSame($loader, $loader->resolve('foo.foo'), '->resolve() finds a loader');
        $this->assertSame($loader1, $loader->resolve('foo.xml'), '->resolve() finds a loader');

        $loader1 = $this->getMock('Symfony_Component_Config_Loader_LoaderInterface');
        $loader1->expects($this->once())->method('supports')->will($this->returnValue(false));
        $resolver = new Symfony_Component_Config_Loader_LoaderResolver(array($loader1));
        $loader = new Symfony_Component_Config_Tests_Loader_ProjectLoader1();
        $loader->setResolver($resolver);
        try {
            $loader->resolve('FOOBAR');
            $this->fail('->resolve() throws a FileLoaderLoadException if the resource cannot be loaded');
        } catch (Symfony_Component_Config_Exception_FileLoaderLoadException $e) {
            $this->assertThat($e, $this->isInstanceOf('Symfony_Component_Config_Exception_FileLoaderLoadException'), '->resolve() throws a FileLoaderLoadException if the resource cannot be loaded');
        }
    }

    public function testImport()
    {
        $loader = $this->getMock('Symfony_Component_Config_Loader_Loader', array('supports', 'load'));
        $loader->expects($this->once())->method('supports')->will($this->returnValue(true));
        $loader->expects($this->once())->method('load')->will($this->returnValue('yes'));

        $this->assertEquals('yes', $loader->import('foo'));
    }
}

class Symfony_Component_Config_Tests_Loader_ProjectLoader1 extends Symfony_Component_Config_Loader_Loader
{
    public function load($resource, $type = null)
    {
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'foo' === pathinfo($resource, PATHINFO_EXTENSION);
    }

    public function getType()
    {
    }
}
