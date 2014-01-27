<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Config_Tests_Loader_LoaderResolverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Symfony_Component_Config_Loader_LoaderResolver::__construct
     */
    public function testConstructor()
    {
        $resolver = new Symfony_Component_Config_Loader_LoaderResolver(array(
            $loader = $this->getMock('Symfony_Component_Config_Loader_LoaderInterface'),
        ));

        $this->assertEquals(array($loader), $resolver->getLoaders(), '__construct() takes an array of loaders as its first argument');
    }

    /**
     * @covers Symfony_Component_Config_Loader_LoaderResolver::resolve
     */
    public function testResolve()
    {
        $loader = $this->getMock('Symfony_Component_Config_Loader_LoaderInterface');
        $resolver = new Symfony_Component_Config_Loader_LoaderResolver(array($loader));
        $this->assertFalse($resolver->resolve('foo.foo'), '->resolve() returns false if no loader is able to load the resource');

        $loader = $this->getMock('Symfony_Component_Config_Loader_LoaderInterface');
        $loader->expects($this->once())->method('supports')->will($this->returnValue(true));
        $resolver = new Symfony_Component_Config_Loader_LoaderResolver(array($loader));
        $this->assertEquals($loader, $resolver->resolve(create_function('', '')), '->resolve() returns the loader for the given resource');
    }

    /**
     * @covers Symfony_Component_Config_Loader_LoaderResolver::getLoaders
     * @covers Symfony_Component_Config_Loader_LoaderResolver::addLoader
     */
    public function testLoaders()
    {
        $resolver = new Symfony_Component_Config_Loader_LoaderResolver();
        $resolver->addLoader($loader = $this->getMock('Symfony_Component_Config_Loader_LoaderInterface'));

        $this->assertEquals(array($loader), $resolver->getLoaders(), 'addLoader() adds a loader');
    }
}
