<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_Loader_PhpFileLoaderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_Config_Loader_Loader')) {
            $this->markTestSkipped('The "Config" component is not available');
        }
    }

    /**
     * @covers Symfony_Component_DependencyInjection_Loader_PhpFileLoader::supports
     */
    public function testSupports()
    {
        $loader = new Symfony_Component_DependencyInjection_Loader_PhpFileLoader(new Symfony_Component_DependencyInjection_ContainerBuilder(), new Symfony_Component_Config_FileLocator());

        $this->assertTrue($loader->supports('foo.php'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');
    }

    /**
     * @covers Symfony_Component_DependencyInjection_Loader_PhpFileLoader::load
     */
    public function testLoad()
    {
        $loader = new Symfony_Component_DependencyInjection_Loader_PhpFileLoader($container = new Symfony_Component_DependencyInjection_ContainerBuilder(), new Symfony_Component_Config_FileLocator());

        $loader->load(dirname(__FILE__).'/../Fixtures/php/simple.php');

        $this->assertEquals('foo', $container->getParameter('foo'), '->load() loads a PHP file resource');
    }
}
