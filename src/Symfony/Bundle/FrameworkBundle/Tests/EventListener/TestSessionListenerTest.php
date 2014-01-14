<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * SessionListenerTest.
 *
 * Tests SessionListener.
 *
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class Symfony_Bundle_FrameworkBundle_Tests_EventListener_TestSessionListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony_Bundle_FrameworkBundle_EventListener_TestSessionListener
     */
    private $listener;

    /**
     * @var Symfony_Component_HttpFoundation_Session_SessionInterface
     */
    private $session;

    protected function setUp()
    {
        $this->listener = new Symfony_Bundle_FrameworkBundle_EventListener_TestSessionListener($this->getMock('Symfony_Component_DependencyInjection_ContainerInterface'));
        $this->session  = $this->getSession();
    }

    protected function tearDown()
    {
        $this->listener = null;
        $this->session = null;
    }

    public function testShouldSaveMasterRequestSession()
    {
        $this->sessionHasBeenStarted();
        $this->sessionMustBeSaved();

        $this->filterResponse(new Symfony_Component_HttpFoundation_Request());
    }

    public function testShouldNotSaveSubRequestSession()
    {
        $this->sessionMustNotBeSaved();

        $this->filterResponse(new Symfony_Component_HttpFoundation_Request(), Symfony_Component_HttpKernel_HttpKernelInterface::SUB_REQUEST);
    }

    public function testDoesNotDeleteCookieIfUsingSessionLifetime()
    {
        $this->sessionHasBeenStarted();

        $params = session_get_cookie_params();
        if (version_compare(phpversion(), '5.2.0', '>=')) {
            session_set_cookie_params(0, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        } else {
            session_set_cookie_params(0, $params['path'], $params['domain'], $params['secure']);
        }

        $response = $this->filterResponse(new Symfony_Component_HttpFoundation_Request(), Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST);
        $cookies = $response->headers->getCookies();

        $this->assertEquals(0, reset($cookies)->getExpiresTime());
    }

    public function testUnstartedSessionIsNotSave()
    {
        $this->sessionHasNotBeenStarted();
        $this->sessionMustNotBeSaved();

        $this->filterResponse(new Symfony_Component_HttpFoundation_Request());
    }

    private function filterResponse(Symfony_Component_HttpFoundation_Request $request, $type = Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST)
    {
        $request->setSession($this->session);
        $response = new Symfony_Component_HttpFoundation_Response();
        $kernel = $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface');
        $event = new Symfony_Component_HttpKernel_Event_FilterResponseEvent($kernel, $request, $type, $response);

        $this->listener->onKernelResponse($event);

        $this->assertSame($response, $event->getResponse());

        return $response;
    }

    private function sessionMustNotBeSaved()
    {
        $this->session->expects($this->never())
            ->method('save');
    }

    private function sessionMustBeSaved()
    {
        $this->session->expects($this->once())
            ->method('save');
    }

    private function sessionHasBeenStarted()
    {
        $this->session->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(true));
    }

    private function sessionHasNotBeenStarted()
    {
        $this->session->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(false));
    }

    private function getSession()
    {
        $mock = $this->getMock('Symfony_Component_HttpFoundation_Session_Session', array(), array(), '', false);

        // set return value for getName()
        $mock->expects($this->any())->method('getName')->will($this->returnValue('MOCKSESSID'));

        return $mock;
    }
}
