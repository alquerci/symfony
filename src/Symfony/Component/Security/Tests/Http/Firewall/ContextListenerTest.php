<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Http_Firewall_ContextListenerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        if (!class_exists('Symfony_Component_HttpKernel_HttpKernel')) {
            $this->markTestSkipped('The "HttpKernel" component is not available');
        }

        $this->securityContext = new Symfony_Component_Security_Core_SecurityContext(
            $this->getMock('Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface'),
            $this->getMock('Symfony_Component_Security_Core_Authorization_AccessDecisionManagerInterface')
        );
    }

    protected function tearDown()
    {
        unset($this->securityContext);
    }

    public function testOnKernelResponseWillAddSession()
    {
        $session = $this->runSessionOnKernelResponse(
            new Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken('test1', 'pass1', 'phpunit'),
            null
        );

        $token = unserialize($session->get('_security_session'));
        $this->assertInstanceOf('Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken', $token);
        $this->assertEquals('test1', $token->getUsername());
    }

    public function testOnKernelResponseWillReplaceSession()
    {
        $session = $this->runSessionOnKernelResponse(
            new Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken('test1', 'pass1', 'phpunit'),
            'C:10:"serialized"'
        );

        $token = unserialize($session->get('_security_session'));
        $this->assertInstanceOf('Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken', $token);
        $this->assertEquals('test1', $token->getUsername());
    }

    public function testOnKernelResponseWillRemoveSession()
    {
        $session = $this->runSessionOnKernelResponse(
            null,
            'C:10:"serialized"'
        );

        $this->assertFalse($session->has('_security_session'));
    }

    public function testOnKernelResponseWithoutSession()
    {
        $this->securityContext->setToken(new Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken('test1', 'pass1', 'phpunit'));
        $request = new Symfony_Component_HttpFoundation_Request();
        $session = new Symfony_Component_HttpFoundation_Session_Session(new Symfony_Component_HttpFoundation_Session_Storage_MockArraySessionStorage());
        $request->setSession($session);

        $event = new Symfony_Component_HttpKernel_Event_FilterResponseEvent(
            $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface'),
            $request,
            Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST,
            new Symfony_Component_HttpFoundation_Response()
        );

        $listener = new Symfony_Component_Security_Http_Firewall_ContextListener($this->securityContext, array(), 'session');
        $listener->onKernelResponse($event);

        $this->assertTrue($session->isStarted());
    }

    public function testOnKernelResponseWithoutSessionNorToken()
    {
        $request = new Symfony_Component_HttpFoundation_Request();
        $session = new Symfony_Component_HttpFoundation_Session_Session(new Symfony_Component_HttpFoundation_Session_Storage_MockArraySessionStorage());
        $request->setSession($session);

        $event = new Symfony_Component_HttpKernel_Event_FilterResponseEvent(
            $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface'),
            $request,
            Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST,
            new Symfony_Component_HttpFoundation_Response()
        );

        $listener = new Symfony_Component_Security_Http_Firewall_ContextListener($this->securityContext, array(), 'session');
        $listener->onKernelResponse($event);

        $this->assertFalse($session->isStarted());
    }

    /**
     * @dataProvider provideInvalidToken
     */
    public function testInvalidTokenInSession($token)
    {
        $context = $this->getMock('Symfony_Component_Security_Core_SecurityContextInterface');
        $event = $this->getMockBuilder('Symfony_Component_HttpKernel_Event_GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->getMock('Symfony_Component_HttpFoundation_Request');
        $session = $this->getMockBuilder('Symfony_Component_HttpFoundation_Session_Session')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $request->expects($this->any())
            ->method('hasPreviousSession')
            ->will($this->returnValue(true));
        $request->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($session));
        $session->expects($this->any())
            ->method('get')
            ->with('_security_key123')
            ->will($this->returnValue(serialize($token)));
        $context->expects($this->once())
            ->method('setToken')
            ->with(null);

        $listener = new Symfony_Component_Security_Http_Firewall_ContextListener($context, array(), 'key123');
        $listener->handle($event);
    }

    public function provideInvalidToken()
    {
        return array(
            array(new stdClass()),
            array(null),
        );
    }

    protected function runSessionOnKernelResponse($newToken, $original = null)
    {
        $session = new Symfony_Component_HttpFoundation_Session_Session(new Symfony_Component_HttpFoundation_Session_Storage_MockArraySessionStorage());

        if ($original !== null) {
            $session->set('_security_session', $original);
        }

        $this->securityContext->setToken($newToken);

        $request = new Symfony_Component_HttpFoundation_Request();
        $request->setSession($session);
        $request->cookies->set('MOCKSESSID', true);

        $event = new Symfony_Component_HttpKernel_Event_FilterResponseEvent(
            $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface'),
            $request,
            Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST,
            new Symfony_Component_HttpFoundation_Response()
        );

        $listener = new Symfony_Component_Security_Http_Firewall_ContextListener($this->securityContext, array(), 'session');
        $listener->onKernelResponse($event);

        return $session;
    }}
