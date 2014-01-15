<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_BrowserKit_Tests_TestClient extends Symfony_Component_BrowserKit_Client
{
    protected $nextResponse = null;
    protected $nextScript = null;

    public function setNextResponse(Symfony_Component_BrowserKit_Response $response)
    {
        $this->nextResponse = $response;
    }

    public function setNextScript($script)
    {
        $this->nextScript = $script;
    }

    protected function doRequest($request)
    {
        if (null === $this->nextResponse) {
            return new Symfony_Component_BrowserKit_Response();
        }

        $response = $this->nextResponse;
        $this->nextResponse = null;

        return $response;
    }

    protected function getScript($request)
    {
        $r = new ReflectionClass('Symfony_Component_BrowserKit_Response');
        $path = $r->getFileName();

        return <<<EOF
<?php

require_once('$path');

echo serialize($this->nextScript);
EOF;
    }
}

class Symfony_Component_BrowserKit_Tests_ClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Symfony_Component_BrowserKit_Client::getHistory
     */
    public function testGetHistory()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient(array(), $history = new Symfony_Component_BrowserKit_History());
        $this->assertSame($history, $client->getHistory(), '->getHistory() returns the History');
    }

    /**
     * @covers Symfony_Component_BrowserKit_Client::getCookieJar
     */
    public function testGetCookieJar()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient(array(), null, $cookieJar = new Symfony_Component_BrowserKit_CookieJar());
        $this->assertSame($cookieJar, $client->getCookieJar(), '->getCookieJar() returns the CookieJar');
    }

    /**
     * @covers Symfony_Component_BrowserKit_Client::getRequest
     */
    public function testGetRequest()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->request('GET', 'http://example.com/');

        $this->assertEquals('http://example.com/', $client->getRequest()->getUri(), '->getCrawler() returns the Request of the last request');
    }

    /**
     * @covers Symfony_Component_BrowserKit_Client::getResponse
     */
    public function testGetResponse()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->setNextResponse(new Symfony_Component_BrowserKit_Response('foo'));
        $client->request('GET', 'http://example.com/');

        $this->assertEquals('foo', $client->getResponse()->getContent(), '->getCrawler() returns the Response of the last request');
    }

    public function testGetContent()
    {
        $json = '{"jsonrpc":"2.0","method":"echo","id":7,"params":["Hello World"]}';

        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->request('POST', 'http://example.com/jsonrpc', array(), array(), array(), $json);
        $this->assertEquals($json, $client->getRequest()->getContent());
    }

    /**
     * @covers Symfony_Component_BrowserKit_Client::getCrawler
     */
    public function testGetCrawler()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->setNextResponse(new Symfony_Component_BrowserKit_Response('foo'));
        $crawler = $client->request('GET', 'http://example.com/');

        $this->assertSame($crawler, $client->getCrawler(), '->getCrawler() returns the Crawler of the last request');
    }

    public function testRequestHttpHeaders()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->request('GET', '/');
        $headers = $client->getRequest()->getServer();
        $this->assertEquals('localhost', $headers['HTTP_HOST'], '->request() sets the HTTP_HOST header');

        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->request('GET', 'http://www.example.com');
        $headers = $client->getRequest()->getServer();
        $this->assertEquals('www.example.com', $headers['HTTP_HOST'], '->request() sets the HTTP_HOST header');

        $client->request('GET', 'https://www.example.com');
        $headers = $client->getRequest()->getServer();
        $this->assertTrue($headers['HTTPS'], '->request() sets the HTTPS header');
    }

    public function testRequestURIConversion()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->request('GET', '/foo');
        $this->assertEquals('http://localhost/foo', $client->getRequest()->getUri(), '->request() converts the URI to an absolute one');

        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->request('GET', 'http://www.example.com');
        $this->assertEquals('http://www.example.com', $client->getRequest()->getUri(), '->request() does not change absolute URIs');

        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->request('GET', 'http://www.example.com/');
        $client->request('GET', '/foo');
        $this->assertEquals('http://www.example.com/foo', $client->getRequest()->getUri(), '->request() uses the previous request for relative URLs');

        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->request('GET', 'http://www.example.com/foo');
        $client->request('GET', '#');
        $this->assertEquals('http://www.example.com/foo#', $client->getRequest()->getUri(), '->request() uses the previous request for #');
        $client->request('GET', '#');
        $this->assertEquals('http://www.example.com/foo#', $client->getRequest()->getUri(), '->request() uses the previous request for #');
        $client->request('GET', '#foo');
        $this->assertEquals('http://www.example.com/foo#foo', $client->getRequest()->getUri(), '->request() uses the previous request for #');

        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->request('GET', 'http://www.example.com/foo/');
        $client->request('GET', 'bar');
        $this->assertEquals('http://www.example.com/foo/bar', $client->getRequest()->getUri(), '->request() uses the previous request for relative URLs');

        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->request('GET', 'http://www.example.com/foo/foobar');
        $client->request('GET', 'bar');
        $this->assertEquals('http://www.example.com/foo/bar', $client->getRequest()->getUri(), '->request() uses the previous request for relative URLs');
    }

    public function testRequestReferer()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->request('GET', 'http://www.example.com/foo/foobar');
        $client->request('GET', 'bar');
        $server = $client->getRequest()->getServer();
        $this->assertEquals('http://www.example.com/foo/foobar', $server['HTTP_REFERER'], '->request() sets the referer');
    }

    public function testRequestHistory()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->request('GET', 'http://www.example.com/foo/foobar');
        $client->request('GET', 'bar');

        $this->assertEquals('http://www.example.com/foo/bar', $client->getHistory()->current()->getUri(), '->request() updates the History');
        $this->assertEquals('http://www.example.com/foo/foobar', $client->getHistory()->back()->getUri(), '->request() updates the History');
    }

    public function testRequestCookies()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->setNextResponse(new Symfony_Component_BrowserKit_Response('<html><a href="/foo">foo</a></html>', 200, array('Set-Cookie' => 'foo=bar')));
        $client->request('GET', 'http://www.example.com/foo/foobar');
        $this->assertEquals(array('foo' => 'bar'), $client->getCookieJar()->allValues('http://www.example.com/foo/foobar'), '->request() updates the CookieJar');

        $client->request('GET', 'bar');
        $this->assertEquals(array('foo' => 'bar'), $client->getCookieJar()->allValues('http://www.example.com/foo/foobar'), '->request() updates the CookieJar');
    }

    public function testClick()
    {
        if (!class_exists('Symfony_Component_DomCrawler_Crawler')) {
            $this->markTestSkipped('The "DomCrawler" component is not available');
        }

        if (!class_exists('Symfony_Component_CssSelector_CssSelector')) {
            $this->markTestSkipped('The "CssSelector" component is not available');
        }

        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->setNextResponse(new Symfony_Component_BrowserKit_Response('<html><a href="/foo">foo</a></html>'));
        $crawler = $client->request('GET', 'http://www.example.com/foo/foobar');

        $client->click($crawler->filter('a')->link());

        $this->assertEquals('http://www.example.com/foo', $client->getRequest()->getUri(), '->click() clicks on links');
    }

    public function testClickForm()
    {
        if (!class_exists('Symfony_Component_DomCrawler_Crawler')) {
            $this->markTestSkipped('The "DomCrawler" component is not available');
        }

        if (!class_exists('Symfony_Component_CssSelector_CssSelector')) {
            $this->markTestSkipped('The "CssSelector" component is not available');
        }

        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->setNextResponse(new Symfony_Component_BrowserKit_Response('<html><form action="/foo"><input type="submit" /></form></html>'));
        $crawler = $client->request('GET', 'http://www.example.com/foo/foobar');

        $client->click($crawler->filter('input')->form());

        $this->assertEquals('http://www.example.com/foo', $client->getRequest()->getUri(), '->click() Form submit forms');
    }

    public function testSubmit()
    {
        if (!class_exists('Symfony_Component_DomCrawler_Crawler')) {
            $this->markTestSkipped('The "DomCrawler" component is not available');
        }

        if (!class_exists('Symfony_Component_CssSelector_CssSelector')) {
            $this->markTestSkipped('The "CssSelector" component is not available');
        }

        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->setNextResponse(new Symfony_Component_BrowserKit_Response('<html><form action="/foo"><input type="submit" /></form></html>'));
        $crawler = $client->request('GET', 'http://www.example.com/foo/foobar');

        $client->submit($crawler->filter('input')->form());

        $this->assertEquals('http://www.example.com/foo', $client->getRequest()->getUri(), '->submit() submit forms');
    }

    public function testSubmitPreserveAuth()
    {
        if (!class_exists('Symfony_Component_DomCrawler_Crawler')) {
            $this->markTestSkipped('The "DomCrawler" component is not available');
        }

        if (!class_exists('Symfony_Component_CssSelector_CssSelector')) {
            $this->markTestSkipped('The "CssSelector" component is not available');
        }

        $client = new Symfony_Component_BrowserKit_Tests_TestClient(array('PHP_AUTH_USER' => 'foo', 'PHP_AUTH_PW' => 'bar'));
        $client->setNextResponse(new Symfony_Component_BrowserKit_Response('<html><form action="/foo"><input type="submit" /></form></html>'));
        $crawler = $client->request('GET', 'http://www.example.com/foo/foobar');

        $server = $client->getRequest()->getServer();
        $this->assertArrayHasKey('PHP_AUTH_USER', $server);
        $this->assertEquals('foo', $server['PHP_AUTH_USER']);
        $this->assertArrayHasKey('PHP_AUTH_PW', $server);
        $this->assertEquals('bar', $server['PHP_AUTH_PW']);

        $client->submit($crawler->filter('input')->form());

        $this->assertEquals('http://www.example.com/foo', $client->getRequest()->getUri(), '->submit() submit forms');

        $server = $client->getRequest()->getServer();
        $this->assertArrayHasKey('PHP_AUTH_USER', $server);
        $this->assertEquals('foo', $server['PHP_AUTH_USER']);
        $this->assertArrayHasKey('PHP_AUTH_PW', $server);
        $this->assertEquals('bar', $server['PHP_AUTH_PW']);
    }

    public function testFollowRedirect()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->followRedirects(false);
        $client->request('GET', 'http://www.example.com/foo/foobar');

        try {
            $client->followRedirect();
            $this->fail('->followRedirect() throws a LogicException if the request was not redirected');
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('LogicException'), '->followRedirect() throws a LogicException if the request was not redirected');
        }

        $client->setNextResponse(new Symfony_Component_BrowserKit_Response('', 302, array('Location' => 'http://www.example.com/redirected')));
        $client->request('GET', 'http://www.example.com/foo/foobar');
        $client->followRedirect();

        $this->assertEquals('http://www.example.com/redirected', $client->getRequest()->getUri(), '->followRedirect() follows a redirect if any');

        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->setNextResponse(new Symfony_Component_BrowserKit_Response('', 302, array('Location' => 'http://www.example.com/redirected')));
        $client->request('GET', 'http://www.example.com/foo/foobar');

        $this->assertEquals('http://www.example.com/redirected', $client->getRequest()->getUri(), '->followRedirect() automatically follows redirects if followRedirects is true');
    }

    public function testFollowRedirectWithCookies()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->followRedirects(false);
        $client->setNextResponse(new Symfony_Component_BrowserKit_Response('', 302, array(
            'Location'   => 'http://www.example.com/redirected',
            'Set-Cookie' => 'foo=bar',
        )));
        $client->request('GET', 'http://www.example.com/');
        $this->assertEquals(array(), $client->getRequest()->getCookies());
        $client->followRedirect();
        $this->assertEquals(array('foo' => 'bar'), $client->getRequest()->getCookies());
    }

    public function testBack()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();

        $parameters = array('foo' => 'bar');
        $files = array('myfile.foo' => 'baz');
        $server = array('X_TEST_FOO' => 'bazbar');
        $content = 'foobarbaz';

        $client->request('GET', 'http://www.example.com/foo/foobar', $parameters, $files, $server, $content);
        $client->request('GET', 'http://www.example.com/foo');
        $client->back();

        $this->assertEquals('http://www.example.com/foo/foobar', $client->getRequest()->getUri(), '->back() goes back in the history');
        $this->assertArrayHasKey('foo', $client->getRequest()->getParameters(), '->back() keeps parameters');
        $this->assertArrayHasKey('myfile.foo', $client->getRequest()->getFiles(), '->back() keeps files');
        $this->assertArrayHasKey('X_TEST_FOO', $client->getRequest()->getServer(), '->back() keeps $_SERVER');
        $this->assertEquals($content, $client->getRequest()->getContent(), '->back() keeps content');
    }

    public function testForward()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();

        $parameters = array('foo' => 'bar');
        $files = array('myfile.foo' => 'baz');
        $server = array('X_TEST_FOO' => 'bazbar');
        $content = 'foobarbaz';

        $client->request('GET', 'http://www.example.com/foo/foobar');
        $client->request('GET', 'http://www.example.com/foo', $parameters, $files, $server, $content);
        $client->back();
        $client->forward();

        $this->assertEquals('http://www.example.com/foo', $client->getRequest()->getUri(), '->forward() goes forward in the history');
        $this->assertArrayHasKey('foo', $client->getRequest()->getParameters(), '->forward() keeps parameters');
        $this->assertArrayHasKey('myfile.foo', $client->getRequest()->getFiles(), '->forward() keeps files');
        $this->assertArrayHasKey('X_TEST_FOO', $client->getRequest()->getServer(), '->forward() keeps $_SERVER');
        $this->assertEquals($content, $client->getRequest()->getContent(), '->forward() keeps content');
    }

    public function testReload()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();

        $parameters = array('foo' => 'bar');
        $files = array('myfile.foo' => 'baz');
        $server = array('X_TEST_FOO' => 'bazbar');
        $content = 'foobarbaz';

        $client->request('GET', 'http://www.example.com/foo/foobar', $parameters, $files, $server, $content);
        $client->reload();

        $this->assertEquals('http://www.example.com/foo/foobar', $client->getRequest()->getUri(), '->reload() reloads the current page');
        $this->assertArrayHasKey('foo', $client->getRequest()->getParameters(), '->reload() keeps parameters');
        $this->assertArrayHasKey('myfile.foo', $client->getRequest()->getFiles(), '->reload() keeps files');
        $this->assertArrayHasKey('X_TEST_FOO', $client->getRequest()->getServer(), '->reload() keeps $_SERVER');
        $this->assertEquals($content, $client->getRequest()->getContent(), '->reload() keeps content');
    }

    public function testRestart()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->request('GET', 'http://www.example.com/foo/foobar');
        $client->restart();

        $this->assertTrue($client->getHistory()->isEmpty(), '->restart() clears the history');
        $this->assertEquals(array(), $client->getCookieJar()->all(), '->restart() clears the cookies');
    }

    public function testInsulatedRequests()
    {
        if (!class_exists('Symfony_Component_Process_Process')) {
            $this->markTestSkipped('The "Process" component is not available');
        }

        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $client->insulate();
        $client->setNextScript("new Symfony_Component_BrowserKit_Response('foobar')");
        $client->request('GET', 'http://www.example.com/foo/foobar');

        $this->assertEquals('foobar', $client->getResponse()->getContent(), '->insulate() process the request in a forked process');

        $client->setNextScript("new Symfony_Component_BrowserKit_Response('foobar)");

        try {
            $client->request('GET', 'http://www.example.com/foo/foobar');
            $this->fail('->request() throws a RuntimeException if the script has an error');
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('RuntimeException'), '->request() throws a RuntimeException if the script has an error');
        }
    }

    public function testGetServerParameter()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();
        $this->assertEquals('localhost', $client->getServerParameter('HTTP_HOST'));
        $this->assertEquals('Symfony2 BrowserKit', $client->getServerParameter('HTTP_USER_AGENT'));
        $this->assertEquals('testvalue', $client->getServerParameter('testkey', 'testvalue'));
    }

    public function testSetServerParameter()
    {
        $client = new Symfony_Component_BrowserKit_Tests_TestClient();

        $this->assertEquals('localhost', $client->getServerParameter('HTTP_HOST'));
        $this->assertEquals('Symfony2 BrowserKit', $client->getServerParameter('HTTP_USER_AGENT'));

        $client->setServerParameter('HTTP_HOST', 'testhost');
        $this->assertEquals('testhost', $client->getServerParameter('HTTP_HOST'));

        $client->setServerParameter('HTTP_USER_AGENT', 'testua');
        $this->assertEquals('testua', $client->getServerParameter('HTTP_USER_AGENT'));
    }
}
