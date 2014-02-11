<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



class Symfony_Component_HttpKernel_Tests_Fragment_FragmentHandlerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderWhenRendererDoesNotExist()
    {
        $handler = new Symfony_Component_HttpKernel_Fragment_FragmentHandler();
        $handler->render('/', 'foo');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderWithUnknownRenderer()
    {
        $handler = $this->getHandler($this->returnValue(new Symfony_Component_HttpFoundation_Response('foo')));

        $handler->render('/', 'bar');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Error when rendering "http://localhost/" (Status code is 404).
     */
    public function testDeliverWithUnsuccessfulResponse()
    {
        $handler = $this->getHandler($this->returnValue(new Symfony_Component_HttpFoundation_Response('foo', 404)));

        $handler->render('/', 'foo');
    }

    public function testRender()
    {
        $handler = $this->getHandler($this->returnValue(new Symfony_Component_HttpFoundation_Response('foo')), array('/', Symfony_Component_HttpFoundation_Request::create('/'), array('foo' => 'foo', 'ignore_errors' => true)));

        $this->assertEquals('foo', $handler->render('/', 'foo', array('foo' => 'foo')));
    }

    /**
     * @dataProvider getFixOptionsData
     */
    public function testFixOptions($expected, $options)
    {
        $handler = new Symfony_Component_HttpKernel_Fragment_FragmentHandler();

        version_compare(PHP_VERSION, '5.3.0', '>=') && set_error_handler(create_function('$errorNumber, $message, $file, $line, $context', 'return $errorNumber & E_USER_DEPRECATED;'));
        $this->assertEquals($expected, $handler->fixOptions($options));
        version_compare(PHP_VERSION, '5.3.0', '>=') && restore_error_handler();
    }

    public function getFixOptionsData()
    {
        return array(
            array(array('strategy' => 'esi'), array('standalone' => true)),
            array(array('strategy' => 'esi'), array('standalone' => 'esi')),
            array(array('strategy' => 'hinclude'), array('standalone' => 'js')),
        );
    }

    protected function getHandler($returnValue, $arguments = array())
    {
        $renderer = $this->getMock('Symfony_Component_HttpKernel_Fragment_FragmentRendererInterface');
        $renderer
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'))
        ;
        $e = $renderer
            ->expects($this->any())
            ->method('render')
            ->will($returnValue)
        ;

        if ($arguments) {
            call_user_func_array(array($e, 'with'), $arguments);
        }

        $handler = new Symfony_Component_HttpKernel_Fragment_FragmentHandler();
        $handler->addRenderer($renderer);

        $event = $this->getMockBuilder('Symfony_Component_HttpKernel_Event_GetResponseEvent')->disableOriginalConstructor()->getMock();
        $event
            ->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue(Symfony_Component_HttpFoundation_Request::create('/')))
        ;
        $handler->onKernelRequest($event);

        return $handler;
    }
}
