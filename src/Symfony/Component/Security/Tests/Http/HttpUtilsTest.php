<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Http_HttpUtilsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        if (!class_exists('Symfony_Component_Routing_Router')) {
            $this->markTestSkipped('The "Routing" component is not available');
        }
    }

    public function testCreateRedirectResponse()
    {
        $utils = new Symfony_Component_Security_Http_HttpUtils($this->getUrlGenerator());

        // absolute path
        $response = $utils->createRedirectResponse($this->getRequest(), '/foobar');
        $this->assertTrue($response->isRedirect('http://localhost/foobar'));
        $this->assertEquals(302, $response->getStatusCode());

        // absolute URL
        $response = $utils->createRedirectResponse($this->getRequest(), 'http://symfony.com/');
        $this->assertTrue($response->isRedirect('http://symfony.com/'));

        // route name
        $utils = new Symfony_Component_Security_Http_HttpUtils($urlGenerator = $this->getMock('Symfony_Component_Routing_Generator_UrlGeneratorInterface'));
        $urlGenerator
            ->expects($this->any())
            ->method('generate')
            ->with('foobar', array(), true)
            ->will($this->returnValue('http://localhost/foo/bar'))
        ;
        $urlGenerator
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($this->getMock('Symfony_Component_Routing_RequestContext')))
        ;
        $response = $utils->createRedirectResponse($this->getRequest(), 'foobar');
        $this->assertTrue($response->isRedirect('http://localhost/foo/bar'));
    }

    public function testCreateRequest()
    {
        $utils = new Symfony_Component_Security_Http_HttpUtils($this->getUrlGenerator());

        // absolute path
        $request = $this->getRequest();
        $request->server->set('Foo', 'bar');
        $subRequest = $utils->createRequest($request, '/foobar');

        $this->assertEquals('GET', $subRequest->getMethod());
        $this->assertEquals('/foobar', $subRequest->getPathInfo());
        $this->assertEquals('bar', $subRequest->server->get('Foo'));

        // route name
        $utils = new Symfony_Component_Security_Http_HttpUtils($urlGenerator = $this->getMock('Symfony_Component_Routing_Generator_UrlGeneratorInterface'));
        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->will($this->returnValue('/foo/bar'))
        ;
        $urlGenerator
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($this->getMock('Symfony_Component_Routing_RequestContext')))
        ;
        $subRequest = $utils->createRequest($this->getRequest(), 'foobar');
        $this->assertEquals('/foo/bar', $subRequest->getPathInfo());

        // absolute URL
        $subRequest = $utils->createRequest($this->getRequest(), 'http://symfony.com/');
        $this->assertEquals('/', $subRequest->getPathInfo());
    }

    public function testCheckRequestPath()
    {
        $utils = new Symfony_Component_Security_Http_HttpUtils($this->getUrlGenerator());

        $this->assertTrue($utils->checkRequestPath($this->getRequest(), '/'));
        $this->assertFalse($utils->checkRequestPath($this->getRequest(), '/foo'));
        $this->assertTrue($utils->checkRequestPath($this->getRequest('/foo%20bar'), '/foo bar'));
        // Plus must not decoded to space
        $this->assertTrue($utils->checkRequestPath($this->getRequest('/foo+bar'), '/foo+bar'));
        // Checking unicode
        $this->assertTrue($utils->checkRequestPath($this->getRequest(urlencode('/вход')), '/вход'));

        $urlMatcher = $this->getMock('Symfony_Component_Routing_Matcher_UrlMatcherInterface');
        $urlMatcher
            ->expects($this->any())
            ->method('match')
            ->will($this->throwException(new Symfony_Component_Routing_Exception_ResourceNotFoundException()))
        ;
        $utils = new Symfony_Component_Security_Http_HttpUtils(null, $urlMatcher);
        $this->assertFalse($utils->checkRequestPath($this->getRequest(), 'foobar'));

        $urlMatcher = $this->getMock('Symfony_Component_Routing_Matcher_UrlMatcherInterface');
        $urlMatcher
            ->expects($this->any())
            ->method('match')
            ->will($this->returnValue(array('_route' => 'foobar')))
        ;
        $utils = new Symfony_Component_Security_Http_HttpUtils(null, $urlMatcher);
        $this->assertTrue($utils->checkRequestPath($this->getRequest('/foo/bar'), 'foobar'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCheckRequestPathWithUrlMatcherLoadingException()
    {
        $urlMatcher = $this->getMock('Symfony_Component_Routing_Matcher_UrlMatcherInterface');
        $urlMatcher
            ->expects($this->any())
            ->method('match')
            ->will($this->throwException(new RuntimeException()))
        ;
        $utils = new Symfony_Component_Security_Http_HttpUtils(null, $urlMatcher);
        $utils->checkRequestPath($this->getRequest(), 'foobar');
    }

    public function testGenerateUriRemovesQueryString()
    {
        $method = new ReflectionMethod('Symfony_Component_Security_Http_HttpUtils', 'generateUri');

        $utils = new Symfony_Component_Security_Http_HttpUtils($this->getUrlGenerator());
        $this->assertEquals('/foo/bar', $method->invoke($utils, new Symfony_Component_HttpFoundation_Request(), 'route_name'));

        $utils = new Symfony_Component_Security_Http_HttpUtils($this->getUrlGenerator('/foo/bar?param=value'));
        $this->assertEquals('/foo/bar', $method->invoke($utils, new Symfony_Component_HttpFoundation_Request(), 'route_name'));
    }

    private function getUrlGenerator($generatedUrl = '/foo/bar')
    {
        $urlGenerator = $this->getMock('Symfony_Component_Routing_Generator_UrlGeneratorInterface');
        $urlGenerator
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnValue($generatedUrl))
        ;

        return $urlGenerator;
    }

    private function getRequest($path = '/')
    {
        return Symfony_Component_HttpFoundation_Request::create($path, 'get');
    }
}
