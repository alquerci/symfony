<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_ContainerAwareHttpKernelTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_DependencyInjection_Container')) {
            $this->markTestSkipped('The "DependencyInjection" component is not available');
        }

        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }
    }

    /**
     * @dataProvider getProviderTypes
     */
    public function testHandle($type)
    {
        $request = new Symfony_Component_HttpFoundation_Request();
        $expected = new Symfony_Component_HttpFoundation_Response();

        $container = $this->getMock('Symfony_Component_DependencyInjection_ContainerInterface');
        $container
            ->expects($this->once())
            ->method('enterScope')
            ->with($this->equalTo('request'))
        ;
        $container
            ->expects($this->once())
            ->method('leaveScope')
            ->with($this->equalTo('request'))
        ;
        $container
            ->expects($this->once())
            ->method('set')
            ->with($this->equalTo('request'), $this->equalTo($request), $this->equalTo('request'))
        ;

        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $resolver = $this->getMock('Symfony_Component_HttpKernel_Controller_ControllerResolverInterface');
        $kernel = new Symfony_Component_HttpKernel_DependencyInjection_ContainerAwareHttpKernel($dispatcher, $container, $resolver);

        $controller = array(new Symfony_Component_HttpKernel_Tests_ContainerAwareHttpKernelTestClosure($expected), 'doReturn');

        $resolver->expects($this->once())
            ->method('getController')
            ->with($request)
            ->will($this->returnValue($controller));
        $resolver->expects($this->once())
            ->method('getArguments')
            ->with($request, $controller)
            ->will($this->returnValue(array()));

        $actual = $kernel->handle($request, $type);

        $this->assertSame($expected, $actual, '->handle() returns the response');
    }

    /**
     * @dataProvider getProviderTypes
     */
    public function testHandleRestoresThePreviousRequestOnException($type)
    {
        $request = new Symfony_Component_HttpFoundation_Request();
        $expected = new Exception();

        $container = $this->getMock('Symfony_Component_DependencyInjection_ContainerInterface');
        $container
            ->expects($this->once())
            ->method('enterScope')
            ->with($this->equalTo('request'))
        ;
        $container
            ->expects($this->once())
            ->method('leaveScope')
            ->with($this->equalTo('request'))
        ;
        $container
            ->expects($this->once())
            ->method('set')
            ->with($this->equalTo('request'), $this->equalTo($request), $this->equalTo('request'))
        ;

        $dispatcher = new Symfony_Component_EventDispatcher_EventDispatcher();
        $resolver = $this->getMock('Symfony_Component_HttpKernel_Controller_ControllerResolverInterface');
        $kernel = new Symfony_Component_HttpKernel_DependencyInjection_ContainerAwareHttpKernel($dispatcher, $container, $resolver);

        $controller = array(new Symfony_Component_HttpKernel_Tests_ContainerAwareHttpKernelTestClosure($expected), 'doThrow');

        $resolver->expects($this->once())
            ->method('getController')
            ->with($request)
            ->will($this->returnValue($controller));
        $resolver->expects($this->once())
            ->method('getArguments')
            ->with($request, $controller)
            ->will($this->returnValue(array()));

        try {
            $kernel->handle($request, $type);
            $this->fail('->handle() suppresses the controller exception');
        } catch (Exception $actual) {
            $this->assertSame($expected, $actual, '->handle() throws the controller exception');
        }
    }

    public function getProviderTypes()
    {
        return array(
            array(Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST),
            array(Symfony_Component_HttpKernel_HttpKernelInterface::SUB_REQUEST),
        );
    }
}

class Symfony_Component_HttpKernel_Tests_ContainerAwareHttpKernelTestClosure
{
    private $expected;

    public function __construct($expected)
    {
        $this->expected = $expected;
    }

    public function doReturn()
    {
        return $this->expected;
    }

    public function doThrow()
    {
        throw $this->expected;
    }
}
