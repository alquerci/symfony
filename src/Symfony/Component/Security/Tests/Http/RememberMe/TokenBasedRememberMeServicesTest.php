<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Http_RememberMe_TokenBasedRememberMeServicesTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }
    }

    public function testAutoLoginReturnsNullWhenNoCookie()
    {
        $service = $this->getService(null, array('name' => 'foo'));

        $this->assertNull($service->autoLogin(new Symfony_Component_HttpFoundation_Request()));
    }

    public function testAutoLoginThrowsExceptionOnInvalidCookie()
    {
        $service = $this->getService(null, array('name' => 'foo', 'path' => null, 'domain' => null, 'always_remember_me' => false, 'remember_me_parameter' => 'foo'));
        $request = new Symfony_Component_HttpFoundation_Request;
        $request->request->set('foo', 'true');
        $request->cookies->set('foo', 'foo');

        $this->assertNull($service->autoLogin($request));
        $this->assertTrue($request->attributes->get(Symfony_Component_Security_Http_RememberMe_RememberMeServicesInterface::COOKIE_ATTR_NAME)->isCleared());
    }

    public function testAutoLoginThrowsExceptionOnNonExistentUser()
    {
        $userProvider = $this->getProvider();
        $service = $this->getService($userProvider, array('name' => 'foo', 'path' => null, 'domain' => null, 'always_remember_me' => true, 'lifetime' => 3600));
        $request = new Symfony_Component_HttpFoundation_Request;
        $request->cookies->set('foo', $this->getCookie('fooclass', 'foouser', time()+3600, 'foopass'));

        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->will($this->throwException(new Symfony_Component_Security_Core_Exception_UsernameNotFoundException('user not found')))
        ;

        $this->assertNull($service->autoLogin($request));
        $this->assertTrue($request->attributes->get(Symfony_Component_Security_Http_RememberMe_RememberMeServicesInterface::COOKIE_ATTR_NAME)->isCleared());
    }

    public function testAutoLoginDoesNotAcceptCookieWithInvalidHash()
    {
        $userProvider = $this->getProvider();
        $service = $this->getService($userProvider, array('name' => 'foo', 'path' => null, 'domain' => null, 'always_remember_me' => true, 'lifetime' => 3600));
        $request = new Symfony_Component_HttpFoundation_Request;
        $request->cookies->set('foo', base64_encode('class:'.base64_encode('foouser').':123456789:fooHash'));

        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $user
            ->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue('foopass'))
        ;

        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($this->equalTo('foouser'))
            ->will($this->returnValue($user))
        ;

        $this->assertNull($service->autoLogin($request));
        $this->assertTrue($request->attributes->get(Symfony_Component_Security_Http_RememberMe_RememberMeServicesInterface::COOKIE_ATTR_NAME)->isCleared());
    }

    public function testAutoLoginDoesNotAcceptAnExpiredCookie()
    {
        $userProvider = $this->getProvider();
        $service = $this->getService($userProvider, array('name' => 'foo', 'path' => null, 'domain' => null, 'always_remember_me' => true, 'lifetime' => 3600));
        $request = new Symfony_Component_HttpFoundation_Request;
        $request->cookies->set('foo', $this->getCookie('fooclass', 'foouser', time() - 1, 'foopass'));

        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $user
            ->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue('foopass'))
        ;

        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($this->equalTo('foouser'))
            ->will($this->returnValue($user))
        ;

        $this->assertNull($service->autoLogin($request));
        $this->assertTrue($request->attributes->get(Symfony_Component_Security_Http_RememberMe_RememberMeServicesInterface::COOKIE_ATTR_NAME)->isCleared());
    }

    public function testAutoLogin()
    {
        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $user
            ->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue(array('ROLE_FOO')))
        ;
        $user
            ->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue('foopass'))
        ;

        $userProvider = $this->getProvider();
        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($this->equalTo('foouser'))
            ->will($this->returnValue($user))
        ;

        $service = $this->getService($userProvider, array('name' => 'foo', 'always_remember_me' => true, 'lifetime' => 3600));
        $request = new Symfony_Component_HttpFoundation_Request;
        $request->cookies->set('foo', $this->getCookie('fooclass', 'foouser', time()+3600, 'foopass'));

        $returnedToken = $service->autoLogin($request);

        $this->assertInstanceOf('Symfony_Component_Security_Core_Authentication_Token_RememberMeToken', $returnedToken);
        $this->assertSame($user, $returnedToken->getUser());
        $this->assertEquals('fookey', $returnedToken->getKey());
    }

    public function testLogout()
    {
        $service = $this->getService(null, array('name' => 'foo', 'path' => null, 'domain' => null));
        $request = new Symfony_Component_HttpFoundation_Request();
        $response = new Symfony_Component_HttpFoundation_Response();
        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');

        $service->logout($request, $response, $token);

        $cookie = $request->attributes->get(Symfony_Component_Security_Http_RememberMe_RememberMeServicesInterface::COOKIE_ATTR_NAME);
        $this->assertTrue($cookie->isCleared());
        $this->assertEquals('/', $cookie->getPath());
        $this->assertNull($cookie->getDomain());
    }

    public function testLoginFail()
    {
        $service = $this->getService(null, array('name' => 'foo', 'path' => '/foo', 'domain' => 'foodomain.foo'));
        $request = new Symfony_Component_HttpFoundation_Request();
        $response = new Symfony_Component_HttpFoundation_Response();

        $service->loginFail($request, $response);

        $cookie = $request->attributes->get(Symfony_Component_Security_Http_RememberMe_RememberMeServicesInterface::COOKIE_ATTR_NAME);
        $this->assertTrue($cookie->isCleared());
        $this->assertEquals('/foo', $cookie->getPath());
        $this->assertEquals('foodomain.foo', $cookie->getDomain());
    }

    public function testLoginSuccessIgnoresTokensWhichDoNotContainAnUserInterfaceImplementation()
    {
        $service = $this->getService(null, array('name' => 'foo', 'always_remember_me' => true, 'path' => null, 'domain' => null));
        $request = new Symfony_Component_HttpFoundation_Request;
        $response = new Symfony_Component_HttpFoundation_Response;
        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue('foo'))
        ;

        $cookies = $response->headers->getCookies();
        $this->assertCount(0, $cookies);

        $service->loginSuccess($request, $response, $token);

        $cookies = $response->headers->getCookies();
        $this->assertCount(0, $cookies);
    }

    public function testLoginSuccess()
    {
        $service = $this->getService(null, array('name' => 'foo', 'domain' => 'myfoodomain.foo', 'path' => '/foo/path', 'secure' => true, 'httponly' => true, 'lifetime' => 3600, 'always_remember_me' => true));
        $request = new Symfony_Component_HttpFoundation_Request;
        $response = new Symfony_Component_HttpFoundation_Response;

        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');
        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $user
            ->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue('foopass'))
        ;
        $user
            ->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue('foouser'))
        ;
        $token
            ->expects($this->atLeastOnce())
            ->method('getUser')
            ->will($this->returnValue($user))
        ;

        $cookies = $response->headers->getCookies();
        $this->assertCount(0, $cookies);

        $service->loginSuccess($request, $response, $token);

        $cookies = $response->headers->getCookies(Symfony_Component_HttpFoundation_ResponseHeaderBag::COOKIES_ARRAY);
        $cookie  = $cookies['myfoodomain.foo']['/foo/path']['foo'];
        $this->assertFalse($cookie->isCleared());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertTrue($cookie->getExpiresTime() > time() + 3590 && $cookie->getExpiresTime() < time() + 3610);
        $this->assertEquals('myfoodomain.foo', $cookie->getDomain());
        $this->assertEquals('/foo/path', $cookie->getPath());
    }

    protected function getCookie($class, $username, $expires, $password)
    {
        $service = $this->getService();
        $r = new ReflectionMethod($service, 'generateCookieValue');

        return $r->invoke($service, $class, $username, $expires, $password);
    }

    protected function encodeCookie(array $parts)
    {
        $service = $this->getService();
        $r = new ReflectionMethod($service, 'encodeCookie');

        return $r->invoke($service, $parts);
    }

    protected function getService($userProvider = null, $options = array(), $logger = null)
    {
        if (null === $userProvider) {
            $userProvider = $this->getProvider();
        }

        $service = new Symfony_Component_Security_Tests_Http_RememberMe_TokenBasedRememberMeServices(array($userProvider), 'fookey', 'fookey', $options, $logger);

        return $service;
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

class Symfony_Component_Security_Tests_Http_RememberMe_TokenBasedRememberMeServices extends Symfony_Component_Security_Http_RememberMe_TokenBasedRememberMeServices
{
    public function cancelCookie(Symfony_Component_HttpFoundation_Request $request)
    {
        return parent::cancelCookie($request);
    }

    public function generateCookieValue($class, $username, $expires, $password)
    {
        return parent::generateCookieValue($class, $username, $expires, $password);
    }
}
