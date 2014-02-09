<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_Loader_ClosureLoaderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_Config_Loader_Loader')) {
            $this->markTestSkipped('The "Config" component is not available');
        }
    }

    /**
     * @covers Symfony_Component_DependencyInjection_Loader_ClosureLoader::supports
     */
    public function testSupports()
    {
        $loader = new Symfony_Component_DependencyInjection_Loader_ClosureLoader(new Symfony_Component_DependencyInjection_ContainerBuilder());

        $this->assertTrue($loader->supports(create_function('$container', '')), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');
    }

    /**
     * @covers Symfony_Component_DependencyInjection_Loader_ClosureLoader::load
     */
    public function testLoad()
    {
        $loader = new Symfony_Component_DependencyInjection_Loader_ClosureLoader($container = new Symfony_Component_DependencyInjection_ContainerBuilder());

        $loader->load(create_function('$container', '
            $container->setParameter("foo", "foo");
        '));

        $this->assertEquals('foo', $container->getParameter('foo'), '->load() loads a Closure resource');
    }
}
