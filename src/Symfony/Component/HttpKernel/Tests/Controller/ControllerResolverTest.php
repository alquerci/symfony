<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_ControllerResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }
    }

    public function testGetController()
    {
        $logger = new Symfony_Component_HttpKernel_Tests_Logger();
        $resolver = new Symfony_Component_HttpKernel_Controller_ControllerResolver($logger);

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $this->assertFalse($resolver->getController($request), '->getController() returns false when the request has no _controller attribute');
        $this->assertEquals(array('Unable to look for the controller as the "_controller" parameter is missing'), $logger->getLogs('warning'));

        $request->attributes->set('_controller', 'Symfony_Component_HttpKernel_Tests_ControllerResolverTest::testGetController');
        $controller = $resolver->getController($request);
        $this->assertInstanceOf('Symfony_Component_HttpKernel_Tests_ControllerResolverTest', $controller[0], '->getController() returns a PHP callable');

        $request->attributes->set('_controller', $lambda = create_function('', ''));
        $controller = $resolver->getController($request);
        $this->assertSame($lambda, $controller);

        $request->attributes->set('_controller', $this);
        $controller = $resolver->getController($request);
        $this->assertSame($this, $controller);

        $request->attributes->set('_controller', 'Symfony_Component_HttpKernel_Tests_ControllerResolverTest');
        $controller = $resolver->getController($request);
        $this->assertInstanceOf('Symfony_Component_HttpKernel_Tests_ControllerResolverTest', $controller);

        $request->attributes->set('_controller', array($this, 'controllerMethod1'));
        $controller = $resolver->getController($request);
        $this->assertSame(array($this, 'controllerMethod1'), $controller);

        $request->attributes->set('_controller', array('Symfony_Component_HttpKernel_Tests_ControllerResolverTest', 'controllerMethod4'));
        $controller = $resolver->getController($request);
        $this->assertSame(array('Symfony_Component_HttpKernel_Tests_ControllerResolverTest', 'controllerMethod4'), $controller);

        $request->attributes->set('_controller', 'Symfony_Component_HttpKernel_Tests_some_controller_function');
        $controller = $resolver->getController($request);
        $this->assertSame('Symfony_Component_HttpKernel_Tests_some_controller_function', $controller);

        $request->attributes->set('_controller', 'foo');
        try {
            $resolver->getController($request);
            $this->fail('->getController() throws an InvalidArgumentException if the _controller attribute is not well-formatted');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->getController() throws an InvalidArgumentException if the _controller attribute is not well-formatted');
        }

        $request->attributes->set('_controller', 'foo::bar');
        try {
            $resolver->getController($request);
            $this->fail('->getController() throws an InvalidArgumentException if the _controller attribute contains a non-existent class');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->getController() throws an InvalidArgumentException if the _controller attribute contains a non-existent class');
        }

        $request->attributes->set('_controller', 'Symfony_Component_HttpKernel_Tests_ControllerResolverTest::bar');
        try {
            $resolver->getController($request);
            $this->fail('->getController() throws an InvalidArgumentException if the _controller attribute contains a non-existent method');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->getController() throws an InvalidArgumentException if the _controller attribute contains a non-existent method');
        }
    }

    public function testGetArguments()
    {
        $resolver = new Symfony_Component_HttpKernel_Controller_ControllerResolver();

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $controller = array(new self(), 'testGetArguments');
        $this->assertEquals(array(), $resolver->getArguments($request, $controller), '->getArguments() returns an empty array if the method takes no arguments');

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $request->attributes->set('foo', 'foo');
        $controller = array(new self(), 'controllerMethod1');
        $this->assertEquals(array('foo'), $resolver->getArguments($request, $controller), '->getArguments() returns an array of arguments for the controller method');

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $request->attributes->set('foo', 'foo');
        $controller = array(new self(), 'controllerMethod2');
        $this->assertEquals(array('foo', null), $resolver->getArguments($request, $controller), '->getArguments() uses default values if present');

        $request->attributes->set('bar', 'bar');
        $this->assertEquals(array('foo', 'bar'), $resolver->getArguments($request, $controller), '->getArguments() overrides default values if provided in the request attributes');

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $request->attributes->set('foo', 'foo');
        $controller = create_function('$foo', '');
        $this->assertEquals(array('foo'), $resolver->getArguments($request, $controller));

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $request->attributes->set('foo', 'foo');
        $controller = create_function('$foo, $bar = "bar"', '');
        $this->assertEquals(array('foo', 'bar'), $resolver->getArguments($request, $controller));

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $request->attributes->set('foo', 'foo');
        $controller = new self();
        $this->assertEquals(array('foo', null), $resolver->getArguments($request, $controller));
        $request->attributes->set('bar', 'bar');
        $this->assertEquals(array('foo', 'bar'), $resolver->getArguments($request, $controller));

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $request->attributes->set('foo', 'foo');
        $request->attributes->set('foobar', 'foobar');
        $controller = 'Symfony_Component_HttpKernel_Tests_some_controller_function';
        $this->assertEquals(array('foo', 'foobar'), $resolver->getArguments($request, $controller));

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $request->attributes->set('foo', 'foo');
        $request->attributes->set('foobar', 'foobar');
        $controller = array(new self(), 'controllerMethod3');

        if (version_compare(PHP_VERSION, '5.3.16', '==')) {
            $this->markTestSkipped('PHP 5.3.16 has a major bug in the Reflection sub-system');
        } else {
            try {
                $resolver->getArguments($request, $controller);
                $this->fail('->getArguments() throws a RuntimeException exception if it cannot determine the argument value');
            } catch (Exception $e) {
                $this->assertInstanceOf('RuntimeException', $e, '->getArguments() throws a RuntimeException exception if it cannot determine the argument value');
            }
        }

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $controller = array(new self(), 'controllerMethod5');
        $this->assertEquals(array($request), $resolver->getArguments($request, $controller), '->getArguments() injects the request');
    }

    public function __invoke($foo, $bar = null)
    {
    }

    protected function controllerMethod1($foo)
    {
    }

    protected function controllerMethod2($foo, $bar = null)
    {
    }

    protected function controllerMethod3($foo, $bar = null, $foobar)
    {
    }

    protected static function controllerMethod4()
    {
    }

    protected function controllerMethod5(Symfony_Component_HttpFoundation_Request $request)
    {
    }
}

function Symfony_Component_HttpKernel_Tests_some_controller_function($foo, $foobar)
{
}
