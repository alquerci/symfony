<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Http_EntryPoint_FormAuthenticationEntryPointTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        if (!class_exists('Symfony_Component_HttpKernel_HttpKernel')) {
            $this->markTestSkipped('The "HttpKernel" component is not available');
        }
    }

    public function testStart()
    {
        $request = $this->getMock('Symfony_Component_HttpFoundation_Request', array(), array(), '', false, false);
        $response = $this->getMock('Symfony_Component_HttpFoundation_Response');

        $httpKernel = $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface');
        $httpUtils = $this->getMock('Symfony_Component_Security_Http_HttpUtils');
        $httpUtils
            ->expects($this->once())
            ->method('createRedirectResponse')
            ->with($this->equalTo($request), $this->equalTo('/the/login/path'))
            ->will($this->returnValue($response))
        ;

        $entryPoint = new Symfony_Component_Security_Http_EntryPoint_FormAuthenticationEntryPoint($httpKernel, $httpUtils, '/the/login/path', false);

        $this->assertEquals($response, $entryPoint->start($request));
    }

    public function testStartWithUseForward()
    {
        $request = $this->getMock('Symfony_Component_HttpFoundation_Request', array(), array(), '', false, false);
        $subRequest = $this->getMock('Symfony_Component_HttpFoundation_Request', array(), array(), '', false, false);
        $response = $this->getMock('Symfony_Component_HttpFoundation_Response');

        $httpUtils = $this->getMock('Symfony_Component_Security_Http_HttpUtils');
        $httpUtils
            ->expects($this->once())
            ->method('createRequest')
            ->with($this->equalTo($request), $this->equalTo('/the/login/path'))
            ->will($this->returnValue($subRequest))
        ;

        $httpKernel = $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface');
        $httpKernel
            ->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($subRequest), $this->equalTo(Symfony_Component_HttpKernel_HttpKernelInterface::SUB_REQUEST))
            ->will($this->returnValue($response))
        ;

        $entryPoint = new Symfony_Component_Security_Http_EntryPoint_FormAuthenticationEntryPoint($httpKernel, $httpUtils, '/the/login/path', true);

        $this->assertEquals($response, $entryPoint->start($request));
    }
}
