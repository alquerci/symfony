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
 * @author Marcin Sikon<marcin.sikon@gmail.com>
 */
class Symfony_Bundle_FrameworkBundle_Tests_Controller_RedirectControllerTest extends Symfony_Bundle_FrameworkBundle_Tests_TestCase
{
    public function testEmptyRoute()
    {
        $container = $this->getMock('Symfony_Component_DependencyInjection_ContainerInterface');

        $controller = new Symfony_Bundle_FrameworkBundle_Controller_RedirectController();
        $controller->setContainer($container);

        $returnResponse = $controller->redirectAction('', true);
        $this->assertInstanceOf('Symfony_Component_HttpFoundation_Response', $returnResponse);
        $this->assertEquals(410, $returnResponse->getStatusCode());

        $returnResponse = $controller->redirectAction('', false);
        $this->assertInstanceOf('Symfony_Component_HttpFoundation_Response', $returnResponse);
        $this->assertEquals(404, $returnResponse->getStatusCode());
    }

    /**
     * @dataProvider provider
     */
    public function testRoute($permanent, $expectedCode)
    {
        $request = new Symfony_Component_HttpFoundation_Request();

        $route = 'new-route';
        $url = '/redirect-url';
        $params = array('additional-parameter' => 'value');
        $attributes = array(
            'route' => $route,
            'permanent' => $permanent,
            '_route' => 'current-route',
            '_route_params' => array(
                'route' => $route,
                'permanent' => $permanent,
            ),
        );
        $attributes['_route_params'] = $attributes['_route_params'] + $params;

        $request->attributes = new Symfony_Component_HttpFoundation_ParameterBag($attributes);

        $router = $this->getMock('Symfony_Component_Routing_RouterInterface');
        $router
            ->expects($this->once())
            ->method('generate')
            ->with($this->equalTo($route), $this->equalTo($params))
            ->will($this->returnValue($url));

        $container = $this->getMock('Symfony_Component_DependencyInjection_ContainerInterface');

        $container
            ->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('request'))
            ->will($this->returnValue($request));

        $container
            ->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('router'))
            ->will($this->returnValue($router));

        $controller = new Symfony_Bundle_FrameworkBundle_Controller_RedirectController();
        $controller->setContainer($container);

        $returnResponse = $controller->redirectAction($route, $permanent);

        $this->assertRedirectUrl($returnResponse, $url);
        $this->assertEquals($expectedCode, $returnResponse->getStatusCode());
    }

    public function provider()
    {
        return array(
            array(true, 301),
            array(false, 302),
        );
    }

    public function testEmptyPath()
    {
        $controller = new Symfony_Bundle_FrameworkBundle_Controller_RedirectController();

        $returnResponse = $controller->urlRedirectAction('', true);
        $this->assertInstanceOf('Symfony_Component_HttpFoundation_Response', $returnResponse);
        $this->assertEquals(410, $returnResponse->getStatusCode());

        $returnResponse = $controller->urlRedirectAction('', false);
        $this->assertInstanceOf('Symfony_Component_HttpFoundation_Response', $returnResponse);
        $this->assertEquals(404, $returnResponse->getStatusCode());
    }

    public function testFullURL()
    {
        $controller = new Symfony_Bundle_FrameworkBundle_Controller_RedirectController();
        $returnResponse = $controller->urlRedirectAction('http://foo.bar/');

        $this->assertRedirectUrl($returnResponse, 'http://foo.bar/');
        $this->assertEquals(302, $returnResponse->getStatusCode());
    }

    public function testUrlRedirectDefaultPortParameters()
    {
        $host = 'www.example.com';
        $baseUrl = '/base';
        $path = '/redirect-path';
        $httpPort = 1080;
        $httpsPort = 1443;

        $expectedUrl = "https://$host:$httpsPort$baseUrl$path";
        $request = $this->createRequestObject('http', $host, $httpPort, $baseUrl);
        $controller = $this->createRedirectController($request, null, $httpsPort);
        $returnValue = $controller->urlRedirectAction($path, false, 'https');
        $this->assertRedirectUrl($returnValue, $expectedUrl);

        $expectedUrl = "http://$host:$httpPort$baseUrl$path";
        $request = $this->createRequestObject('https', $host, $httpPort, $baseUrl);
        $controller = $this->createRedirectController($request, $httpPort);
        $returnValue = $controller->urlRedirectAction($path, false, 'http');
        $this->assertRedirectUrl($returnValue, $expectedUrl);
    }

    public function urlRedirectProvider()
    {
        return array(
            // Standard ports
            array('http',  null, null,  'http',  80,   ""),
            array('http',  80,   null,  'http',  80,   ""),
            array('https', null, null,  'http',  80,   ""),
            array('https', 80,   null,  'http',  80,   ""),

            array('http',  null,  null, 'https', 443,  ""),
            array('http',  null,  443,  'https', 443,  ""),
            array('https', null,  null, 'https', 443,  ""),
            array('https', null,  443,  'https', 443,  ""),

            // Non-standard ports
            array('http',  null,  null, 'http',  8080, ":8080"),
            array('http',  4080,  null, 'http',  8080, ":4080"),
            array('http',  80,    null, 'http',  8080, ""),
            array('https', null,  null, 'http',  8080, ""),
            array('https', null,  8443, 'http',  8080, ":8443"),
            array('https', null,  443,  'http',  8080, ""),

            array('https', null,  null, 'https', 8443, ":8443"),
            array('https', null,  4443, 'https', 8443, ":4443"),
            array('https', null,  443,  'https', 8443, ""),
            array('http',  null,  null, 'https', 8443, ""),
            array('http',  8080,  4443, 'https', 8443, ":8080"),
            array('http',  80,    4443, 'https', 8443, ""),
        );
    }

    /**
     * @dataProvider urlRedirectProvider
     */
    public function testUrlRedirect($scheme, $httpPort, $httpsPort, $requestScheme, $requestPort, $expectedPort)
    {
        $host = 'www.example.com';
        $baseUrl = '/base';
        $path = '/redirect-path';
        $expectedUrl = "$scheme://$host$expectedPort$baseUrl$path";

        $request = $this->createRequestObject($requestScheme, $host, $requestPort, $baseUrl);
        $controller = $this->createRedirectController($request);

        $returnValue = $controller->urlRedirectAction($path, false, $scheme, $httpPort, $httpsPort);
        $this->assertRedirectUrl($returnValue, $expectedUrl);
    }

    private function createRequestObject($scheme, $host, $port, $baseUrl)
    {
        $request = $this->getMock('Symfony_Component_HttpFoundation_Request');
        $request
            ->expects($this->any())
            ->method('getScheme')
            ->will($this->returnValue($scheme));
        $request
            ->expects($this->any())
            ->method('getHost')
            ->will($this->returnValue($host));
        $request
            ->expects($this->any())
            ->method('getPort')
            ->will($this->returnValue($port));
        $request
            ->expects($this->any())
            ->method('getBaseUrl')
            ->will($this->returnValue($baseUrl));

        return $request;
    }

    private function createRedirectController(Symfony_Component_HttpFoundation_Request $request, $httpPort = null, $httpsPort = null)
    {
        $container = $this->getMock('Symfony_Component_DependencyInjection_ContainerInterface');
        $container
            ->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('request'))
            ->will($this->returnValue($request));
        if (null !== $httpPort) {
            $container
                ->expects($this->once())
                ->method('hasParameter')
                ->with($this->equalTo('request_listener.http_port'))
                ->will($this->returnValue(true));
            $container
                ->expects($this->once())
                ->method('getParameter')
                ->with($this->equalTo('request_listener.http_port'))
                ->will($this->returnValue($httpPort));
        }
        if (null !== $httpsPort) {
            $container
                ->expects($this->once())
                ->method('hasParameter')
                ->with($this->equalTo('request_listener.https_port'))
                ->will($this->returnValue(true));
            $container
                ->expects($this->once())
                ->method('getParameter')
                ->with($this->equalTo('request_listener.https_port'))
                ->will($this->returnValue($httpsPort));
        }

        $controller = new Symfony_Bundle_FrameworkBundle_Controller_RedirectController();
        $controller->setContainer($container);

        return $controller;
    }

    public function assertRedirectUrl(Symfony_Component_HttpFoundation_Response $returnResponse, $expectedUrl)
    {
        $this->assertTrue($returnResponse->isRedirect($expectedUrl), "Expected: $expectedUrl\nGot:      ".$returnResponse->headers->get('Location'));
    }
}
