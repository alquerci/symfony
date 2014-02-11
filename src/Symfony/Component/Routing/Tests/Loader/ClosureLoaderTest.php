<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Routing_Tests_Loader_ClosureLoaderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_Config_FileLocator')) {
            $this->markTestSkipped('The "Config" component is not available');
        }
    }

    public function testSupports()
    {
        $loader = new Symfony_Component_Routing_Loader_ClosureLoader();

        $closure = create_function('', '');

        $this->assertTrue($loader->supports($closure), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');

        $this->assertTrue($loader->supports($closure, 'closure'), '->supports() checks the resource type if specified');
        $this->assertFalse($loader->supports($closure, 'foo'), '->supports() checks the resource type if specified');
    }

    public function testLoad()
    {
        $loader = new Symfony_Component_Routing_Loader_ClosureLoader();

        $route = new Symfony_Component_Routing_Route('/');
        $routes = $loader->load(array(new Symfony_Component_Routing_Tests_Loader_ClosureLoaderTestClosure($route), '__invoke'));

        $this->assertEquals($route, $routes->get('foo'), '->load() loads a Closure resource');
    }
}

class Symfony_Component_Routing_Tests_Loader_ClosureLoaderTestClosure
{
    private $route;

    public function __construct($route)
    {
        $this->route = $route;
    }

    public function __invoke()
    {
        $routes = new Symfony_Component_Routing_RouteCollection();

        $routes->add('foo', $this->route);

        return $routes;
    }
}
