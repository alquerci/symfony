<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Routing_RoutingTest extends PHPUnit_Framework_TestCase
{
    public function testDefaultsPlaceholders()
    {
        $routes = new Symfony_Component_Routing_RouteCollection();

        $routes->add('foo', new Symfony_Component_Routing_Route(
            '/foo',
            array(
                'foo'    => 'before_%parameter.foo%',
                'bar'    => '%parameter.bar%_after',
                'baz'    => '%%unescaped%%',
                'boo'    => array('%parameter%', '%%escaped_parameter%%', array('%bee_parameter%', 'bee')),
                'bee'    => array('bee', 'bee'),
            ),
            array(
            )
        ));

        $sc = $this->getServiceContainer($routes);

        $sc->expects($this->at(1))->method('hasParameter')->will($this->returnValue(true));
        $sc->expects($this->at(2))->method('getParameter')->will($this->returnValue('foo'));
        $sc->expects($this->at(3))->method('hasParameter')->will($this->returnValue(true));
        $sc->expects($this->at(4))->method('getParameter')->will($this->returnValue('bar'));

        $sc->expects($this->at(5))->method('hasParameter')->will($this->returnValue(true));
        $sc->expects($this->at(6))->method('getParameter')->will($this->returnValue('boo'));

        $sc->expects($this->at(7))->method('hasParameter')->will($this->returnValue(true));
        $sc->expects($this->at(8))->method('getParameter')->will($this->returnValue('foo_bee'));

        $router = new Symfony_Bundle_FrameworkBundle_Routing_Router($sc, 'foo');
        $route = $router->getRouteCollection()->get('foo');

        $this->assertEquals(
            array(
                'foo' => 'before_foo',
                'bar' => 'bar_after',
                'baz' => '%unescaped%',
                'boo' => array('boo', '%escaped_parameter%', array('foo_bee', 'bee')),
                'bee' => array('bee', 'bee'),
            ),
            $route->getDefaults()
        );
    }

    public function testRequirementsPlaceholders()
    {
        $routes = new Symfony_Component_Routing_RouteCollection();

        $routes->add('foo', new Symfony_Component_Routing_Route(
            '/foo',
            array(
            ),
            array(
                'foo'    => 'before_%parameter.foo%',
                'bar'    => '%parameter.bar%_after',
                'baz'    => '%%unescaped%%',
            )
        ));

        $sc = $this->getServiceContainer($routes);

        $sc->expects($this->at(1))->method('hasParameter')->with('parameter.foo')->will($this->returnValue(true));
        $sc->expects($this->at(2))->method('getParameter')->with('parameter.foo')->will($this->returnValue('foo'));
        $sc->expects($this->at(3))->method('hasParameter')->with('parameter.bar')->will($this->returnValue(true));
        $sc->expects($this->at(4))->method('getParameter')->with('parameter.bar')->will($this->returnValue('bar'));

        $router = new Symfony_Bundle_FrameworkBundle_Routing_Router($sc, 'foo');
        $route = $router->getRouteCollection()->get('foo');

        $this->assertEquals(
            array(
                'foo' => 'before_foo',
                'bar' => 'bar_after',
                'baz' => '%unescaped%',
            ),
            $route->getRequirements()
        );
    }

    public function testPatternPlaceholders()
    {
        $routes = new Symfony_Component_Routing_RouteCollection();

        $routes->add('foo', new Symfony_Component_Routing_Route('/before/%parameter.foo%/after/%%unescaped%%'));

        $sc = $this->getServiceContainer($routes);

        $sc->expects($this->at(1))->method('hasParameter')->with('parameter.foo')->will($this->returnValue(true));
        $sc->expects($this->at(2))->method('getParameter')->with('parameter.foo')->will($this->returnValue('foo'));

        $router = new Symfony_Bundle_FrameworkBundle_Routing_Router($sc, 'foo');
        $route = $router->getRouteCollection()->get('foo');

        $this->assertEquals(
            '/before/foo/after/%unescaped%',
            $route->getPath()
        );
    }

    public function testHostPlaceholders()
    {
        $routes = new Symfony_Component_Routing_RouteCollection();

        $route = new Symfony_Component_Routing_Route('foo');
        $route->setHost('/before/%parameter.foo%/after/%%unescaped%%');

        $routes->add('foo', $route);

        $sc = $this->getServiceContainer($routes);

        $sc->expects($this->at(1))->method('hasParameter')->with('parameter.foo')->will($this->returnValue(true));
        $sc->expects($this->at(2))->method('getParameter')->with('parameter.foo')->will($this->returnValue('foo'));

        $router = new Symfony_Bundle_FrameworkBundle_Routing_Router($sc, 'foo');
        $route = $router->getRouteCollection()->get('foo');

        $this->assertEquals(
            '/before/foo/after/%unescaped%',
            $route->getHost()
        );
    }

    /**
     * @expectedException Symfony_Component_DependencyInjection_Exception_ParameterNotFoundException
     * @expectedExceptionMessage You have requested a non-existent parameter "nope".
     */
    public function testExceptionOnNonExistentParameter()
    {
        $routes = new Symfony_Component_Routing_RouteCollection();

        $routes->add('foo', new Symfony_Component_Routing_Route('/%nope%'));

        $sc = $this->getServiceContainer($routes);

        $sc->expects($this->at(1))->method('hasParameter')->with('nope')->will($this->returnValue(false));

        $router = new Symfony_Bundle_FrameworkBundle_Routing_Router($sc, 'foo');
        $router->getRouteCollection()->get('foo');
    }

    /**
     * @expectedException Symfony_Component_DependencyInjection_Exception_RuntimeException
     * @expectedExceptionMessage  A string value must be composed of strings and/or numbers,but found parameter "object" of type object inside string value "/%object%".
     */
    public function testExceptionOnNonStringParameter()
    {
        $routes = new Symfony_Component_Routing_RouteCollection();

        $routes->add('foo', new Symfony_Component_Routing_Route('/%object%'));

        $sc = $this->getServiceContainer($routes);

        $sc->expects($this->at(1))->method('hasParameter')->with('object')->will($this->returnValue(true));
        $sc->expects($this->at(2))->method('getParameter')->with('object')->will($this->returnValue(new stdClass()));

        $router = new Symfony_Bundle_FrameworkBundle_Routing_Router($sc, 'foo');
        $router->getRouteCollection()->get('foo');
    }

    /**
     * @dataProvider getNonStringValues
     */
    public function testDefaultValuesAsNonStrings($value)
    {
        $routes = new Symfony_Component_Routing_RouteCollection();
        $routes->add('foo', new Symfony_Component_Routing_Route('foo', array('foo' => $value), array('foo' => '\d+')));

        $sc = $this->getServiceContainer($routes);

        $router = new Symfony_Bundle_FrameworkBundle_Routing_Router($sc, 'foo');

        $route = $router->getRouteCollection()->get('foo');

        $this->assertSame($value, $route->getDefault('foo'));
    }

    public function getNonStringValues()
    {
        return array(array(null), array(false), array(true), array(new stdClass()), array(array('foo', 'bar')), array(array(array())));
    }

    private function getServiceContainer(Symfony_Component_Routing_RouteCollection $routes)
    {
        $loader = $this->getMock('Symfony_Component_Config_Loader_LoaderInterface');

        $loader
            ->expects($this->any())
            ->method('load')
            ->will($this->returnValue($routes))
        ;

        $sc = $this->getMock('Symfony_Component_DependencyInjection_ContainerInterface');

        $sc
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($loader))
        ;

        return $sc;
    }
}
