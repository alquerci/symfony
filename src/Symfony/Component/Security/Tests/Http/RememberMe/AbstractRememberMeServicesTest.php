<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Http_RememberMe_AbstractRememberMeServicesTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }
    }

    public function testGetRememberMeParameter()
    {
        $service = $this->getService(null, array('remember_me_parameter' => 'foo'));

        $this->assertEquals('foo', $service->getRememberMeParameter());
    }

    public function testGetKey()
    {
        $service = $this->getService();
        $this->assertEquals('fookey', $service->getKey());
    }

    public function testAutoLoginReturnsNullWhenNoCookie()
    {
        $service = $this->getService(null, array('name' => 'foo', 'path' => null, 'domain' => null));

        $this->assertNull($service->autoLogin(new Symfony_Component_HttpFoundation_Request()));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testAutoLoginThrowsExceptionWhenImplementationDoesNotReturnUserInterface()
    {
        $service = $this->getService(null, array('name' => 'foo', 'path' => null, 'domain' => null));
        $request = new Symfony_Component_HttpFoundation_Request;
        $request->cookies->set('foo', 'foo');

        $service
            ->expects($this->once())
            ->method('processAutoLoginCookie')
            ->will($this->returnValue(null))
        ;

        $service->autoLogin($request);
    }

    public function testAutoLogin()
    {
        $service = $this->getService(null, array('name' => 'foo', 'path' => null, 'domain' => null));
        $request = new Symfony_Component_HttpFoundation_Request();
        $request->cookies->set('foo', 'foo');

        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $user
            ->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue(array()))
        ;

        $service
            ->expects($this->once())
            ->method('processAutoLoginCookie')
            ->will($this->returnValue($user))
        ;

        $returnedToken = $service->autoLogin($request);

        $this->assertSame($user, $returnedToken->getUser());
        $this->assertSame('fookey', $returnedToken->getKey());
        $this->assertSame('fookey', $returnedToken->getProviderKey());
    }

    public function testLogout()
    {
        $service = $this->getService(null, array('name' => 'foo', 'path' => null, 'domain' => null));
        $request = new Symfony_Component_HttpFoundation_Request();
        $response = new Symfony_Component_HttpFoundation_Response();
        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');

        $service->logout($request, $response, $token);

        $this->assertTrue($request->attributes->get(Symfony_Component_Security_Http_RememberMe_RememberMeServicesInterface::COOKIE_ATTR_NAME)->isCleared());
    }

    public function testLoginFail()
    {
        $service = $this->getService(null, array('name' => 'foo', 'path' => null, 'domain' => null));
        $request = new Symfony_Component_HttpFoundation_Request();

        $service->loginFail($request);

        $this->assertTrue($request->attributes->get(Symfony_Component_Security_Http_RememberMe_RememberMeServicesInterface::COOKIE_ATTR_NAME)->isCleared());
    }

    public function testLoginSuccessIsNotProcessedWhenTokenDoesNotContainUserInterfaceImplementation()
    {
        $service = $this->getService(null, array('name' => 'foo', 'always_remember_me' => true, 'path' => null, 'domain' => null));
        $request = new Symfony_Component_HttpFoundation_Request;
        $response = new Symfony_Component_HttpFoundation_Response;
        $account = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue('foo'))
        ;

        $service
            ->expects($this->never())
            ->method('onLoginSuccess')
        ;

        $this->assertFalse($request->request->has('foo'));

        $service->loginSuccess($request, $response, $token);
    }

    public function testLoginSuccessIsNotProcessedWhenRememberMeIsNotRequested()
    {
        $service = $this->getService(null, array('name' => 'foo', 'always_remember_me' => false, 'remember_me_parameter' => 'foo', 'path' => null, 'domain' => null));
        $request = new Symfony_Component_HttpFoundation_Request;
        $response = new Symfony_Component_HttpFoundation_Response;
        $account = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($account))
        ;

        $service
            ->expects($this->never())
            ->method('onLoginSuccess')
            ->will($this->returnValue(null))
        ;

        $this->assertFalse($request->request->has('foo'));

        $service->loginSuccess($request, $response, $token);
    }

    public function testLoginSuccessWhenRememberMeAlwaysIsTrue()
    {
        $service = $this->getService(null, array('name' => 'foo', 'always_remember_me' => true, 'path' => null, 'domain' => null));
        $request = new Symfony_Component_HttpFoundation_Request;
        $response = new Symfony_Component_HttpFoundation_Response;
        $account = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($account))
        ;

        $service
            ->expects($this->once())
            ->method('onLoginSuccess')
            ->will($this->returnValue(null))
        ;

        $service->loginSuccess($request, $response, $token);
    }

    /**
     * @dataProvider getPositiveRememberMeParameterValues
     */
    public function testLoginSuccessWhenRememberMeParameterWithPathIsPositive($value)
    {
        $service = $this->getService(null, array('name' => 'foo', 'always_remember_me' => false, 'remember_me_parameter' => 'foo[bar]', 'path' => null, 'domain' => null));

        $request = new Symfony_Component_HttpFoundation_Request;
        $request->request->set('foo', array('bar' => $value));
        $response = new Symfony_Component_HttpFoundation_Response;
        $account = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($account))
        ;

        $service
            ->expects($this->once())
            ->method('onLoginSuccess')
            ->will($this->returnValue(true))
        ;

        $service->loginSuccess($request, $response, $token);
    }

    /**
     * @dataProvider getPositiveRememberMeParameterValues
     */
    public function testLoginSuccessWhenRememberMeParameterIsPositive($value)
    {
        $service = $this->getService(null, array('name' => 'foo', 'always_remember_me' => false, 'remember_me_parameter' => 'foo', 'path' => null, 'domain' => null));

        $request = new Symfony_Component_HttpFoundation_Request;
        $request->request->set('foo', $value);
        $response = new Symfony_Component_HttpFoundation_Response;
        $account = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($account))
        ;

        $service
            ->expects($this->once())
            ->method('onLoginSuccess')
            ->will($this->returnValue(true))
        ;

        $service->loginSuccess($request, $response, $token);
    }

    public function getPositiveRememberMeParameterValues()
    {
        return array(
            array('true'),
            array('1'),
            array('on'),
            array('yes'),
        );
    }

    protected function getService($userProvider = null, $options = array(), $logger = null)
    {
        if (null === $userProvider) {
            $userProvider = $this->getProvider();
        }

        return $this->getMockForAbstractClass('Symfony_Component_Security_Http_RememberMe_AbstractRememberMeServices', array(
            array($userProvider), 'fookey', 'fookey', $options, $logger
        ));
    }

    protected function getProvider()
    {
        $provider = $this->getMock('Symfony_Component_Security_Core_User_UserProviderInterface');
        $provider
            ->expects($this->any())
            ->method('supportsClass')
            ->will($this->returnValue(true))
        ;

        return $provider;
    }
}
