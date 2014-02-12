<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Http_Firewall_RememberMeListenerTest extends PHPUnit_Framework_TestCase
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
    }

    public function testOnCoreSecurityDoesNotTryToPopulateNonEmptySecurityContext()
    {
        list($listener, $context, $service,,) = $this->getListener();

        $context
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface')))
        ;

        $context
            ->expects($this->never())
            ->method('setToken')
        ;

        $this->assertNull($listener->handle($this->getGetResponseEvent()));
    }

    public function testOnCoreSecurityDoesNothingWhenNoCookieIsSet()
    {
        list($listener, $context, $service,,) = $this->getListener();

        $context
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue(null))
        ;

        $service
            ->expects($this->once())
            ->method('autoLogin')
            ->will($this->returnValue(null))
        ;

        $event = $this->getGetResponseEvent();
        $event
            ->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue(new Symfony_Component_HttpFoundation_Request()))
        ;

        $this->assertNull($listener->handle($event));
    }

    public function testOnCoreSecurityIgnoresAuthenticationExceptionThrownByAuthenticationManagerImplementation()
    {
        list($listener, $context, $service, $manager,) = $this->getListener();

        $context
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue(null))
        ;

        $service
            ->expects($this->once())
            ->method('autoLogin')
            ->will($this->returnValue($this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface')))
        ;

        $service
            ->expects($this->once())
            ->method('loginFail')
        ;

        $exception = new Symfony_Component_Security_Core_Exception_AuthenticationException('Authentication failed.');
        $manager
            ->expects($this->once())
            ->method('authenticate')
            ->will($this->throwException($exception))
        ;

        $event = $this->getGetResponseEvent();
        $event
            ->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue(new Symfony_Component_HttpFoundation_Request()))
        ;

        $listener->handle($event);
    }

    public function testOnCoreSecurity()
    {
        list($listener, $context, $service, $manager,) = $this->getListener();

        $context
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue(null))
        ;

        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');
        $service
            ->expects($this->once())
            ->method('autoLogin')
            ->will($this->returnValue($token))
        ;

        $context
            ->expects($this->once())
            ->method('setToken')
            ->with($this->equalTo($token))
        ;

        $manager
            ->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue($token))
        ;

        $event = $this->getGetResponseEvent();
        $event
            ->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue(new Symfony_Component_HttpFoundation_Request()))
        ;

        $listener->handle($event);
    }

    protected function getGetResponseEvent()
    {
        return $this->getMock('Symfony_Component_HttpKernel_Event_GetResponseEvent', array(), array(), '', false);
    }

    protected function getFilterResponseEvent()
    {
        return $this->getMock('Symfony_Component_HttpKernel_Event_FilterResponseEvent', array(), array(), '', false);
    }

    protected function getListener()
    {
        $listener = new Symfony_Component_Security_Http_Firewall_RememberMeListener(
            $context = $this->getContext(),
            $service = $this->getService(),
            $manager = $this->getManager(),
            $logger = $this->getLogger()
        );

        return array($listener, $context, $service, $manager, $logger);
    }

    protected function getLogger()
    {
        return $this->getMock('Psr_Log_LoggerInterface');
    }

    protected function getManager()
    {
        return $this->getMock('Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface');
    }

    protected function getService()
    {
        return $this->getMock('Symfony_Component_Security_Http_RememberMe_RememberMeServicesInterface');
    }

    protected function getContext()
    {
        return $this->getMockBuilder('Symfony_Component_Security_Core_SecurityContext')
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
