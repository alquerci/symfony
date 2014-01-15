<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_BrowserKit_Tests_CookieJarTest extends PHPUnit_Framework_TestCase
{
    public function testSetGet()
    {
        $cookieJar = new Symfony_Component_BrowserKit_CookieJar();
        $cookieJar->set($cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar'));

        $this->assertEquals($cookie, $cookieJar->get('foo'), '->set() sets a cookie');

        $this->assertNull($cookieJar->get('foobar'), '->get() returns null if the cookie does not exist');

        $cookieJar->set($cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar', time() - 86400));
        $this->assertNull($cookieJar->get('foo'), '->get() returns null if the cookie is expired');
    }

    public function testExpire()
    {
        $cookieJar = new Symfony_Component_BrowserKit_CookieJar();
        $cookieJar->set($cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar'));
        $cookieJar->expire('foo');
        $this->assertNull($cookieJar->get('foo'), '->get() returns null if the cookie is expired');
    }

    public function testAll()
    {
        $cookieJar = new Symfony_Component_BrowserKit_CookieJar();
        $cookieJar->set($cookie1 = new Symfony_Component_BrowserKit_Cookie('foo', 'bar'));
        $cookieJar->set($cookie2 = new Symfony_Component_BrowserKit_Cookie('bar', 'foo'));

        $this->assertEquals(array($cookie1, $cookie2), $cookieJar->all(), '->all() returns all cookies in the jar');
    }

    public function testClear()
    {
        $cookieJar = new Symfony_Component_BrowserKit_CookieJar();
        $cookieJar->set($cookie1 = new Symfony_Component_BrowserKit_Cookie('foo', 'bar'));
        $cookieJar->set($cookie2 = new Symfony_Component_BrowserKit_Cookie('bar', 'foo'));

        $cookieJar->clear();

        $this->assertEquals(array(), $cookieJar->all(), '->clear() expires all cookies');
    }

    public function testUpdateFromResponse()
    {
        $response = new Symfony_Component_BrowserKit_Response('', 200, array('Set-Cookie' => 'foo=foo'));

        $cookieJar = new Symfony_Component_BrowserKit_CookieJar();
        $cookieJar->updateFromResponse($response);

        $this->assertEquals('foo', $cookieJar->get('foo')->getValue(), '->updateFromResponse() updates cookies from a Response objects');
    }

    public function testUpdateFromSetCookie()
    {
        $setCookies = array('foo=foo');

        $cookieJar = new Symfony_Component_BrowserKit_CookieJar();
        $cookieJar->set(new Symfony_Component_BrowserKit_Cookie('bar', 'bar'));
        $cookieJar->updateFromSetCookie($setCookies);

        $this->assertThat($cookieJar->get('foo'), $this->isInstanceOf('Symfony_Component_BrowserKit_Cookie'));
        $this->assertThat($cookieJar->get('bar'), $this->isInstanceOf('Symfony_Component_BrowserKit_Cookie'));
        $this->assertEquals('foo', $cookieJar->get('foo')->getValue(), '->updateFromSetCookie() updates cookies from a Set-Cookie header');
        $this->assertEquals('bar', $cookieJar->get('bar')->getValue(), '->updateFromSetCookie() keeps existing cookies');
    }

    public function testUpdateFromEmptySetCookie()
    {
        $cookieJar = new Symfony_Component_BrowserKit_CookieJar();
        $cookieJar->updateFromSetCookie(array(''));
        $this->assertEquals(array(), $cookieJar->all());
    }

    public function testUpdateFromSetCookieWithMultipleCookies()
    {
        $timestamp = time() + 3600;
        $date = gmdate('D, d M Y H:i:s \G\M\T', $timestamp);
        $setCookies = array(sprintf('foo=foo; expires=%s; domain=.symfony.com; path=/, bar=bar; domain=.blog.symfony.com, PHPSESSID=id; expires=%s', $date, $date));

        $cookieJar = new Symfony_Component_BrowserKit_CookieJar();
        $cookieJar->updateFromSetCookie($setCookies);

        $fooCookie = $cookieJar->get('foo', '/', '.symfony.com');
        $barCookie = $cookieJar->get('bar', '/', '.blog.symfony.com');
        $phpCookie = $cookieJar->get('PHPSESSID');

        $this->assertThat($fooCookie, $this->isInstanceOf('Symfony_Component_BrowserKit_Cookie'));
        $this->assertThat($barCookie, $this->isInstanceOf('Symfony_Component_BrowserKit_Cookie'));
        $this->assertThat($phpCookie, $this->isInstanceOf('Symfony_Component_BrowserKit_Cookie'));
        $this->assertEquals('foo', $fooCookie->getValue());
        $this->assertEquals('bar', $barCookie->getValue());
        $this->assertEquals('id', $phpCookie->getValue());
        $this->assertEquals($timestamp, $fooCookie->getExpiresTime());
        $this->assertNull($barCookie->getExpiresTime());
        $this->assertEquals($timestamp, $phpCookie->getExpiresTime());
    }

    /**
     * @dataProvider provideAllValuesValues
     */
    public function testAllValues($uri, $values)
    {
        $cookieJar = new Symfony_Component_BrowserKit_CookieJar();
        $cookieJar->set($cookie1 = new Symfony_Component_BrowserKit_Cookie('foo_nothing', 'foo'));
        $cookieJar->set($cookie2 = new Symfony_Component_BrowserKit_Cookie('foo_expired', 'foo', time() - 86400));
        $cookieJar->set($cookie3 = new Symfony_Component_BrowserKit_Cookie('foo_path', 'foo', null, '/foo'));
        $cookieJar->set($cookie4 = new Symfony_Component_BrowserKit_Cookie('foo_domain', 'foo', null, '/', '.example.com'));
        $cookieJar->set($cookie4 = new Symfony_Component_BrowserKit_Cookie('foo_strict_domain', 'foo', null, '/', '.www4.example.com'));
        $cookieJar->set($cookie5 = new Symfony_Component_BrowserKit_Cookie('foo_secure', 'foo', null, '/', '', true));

        $this->assertEquals($values, array_keys($cookieJar->allValues($uri)), '->allValues() returns the cookie for a given URI');
    }

    public function provideAllValuesValues()
    {
        return array(
            array('http://www.example.com', array('foo_nothing', 'foo_domain')),
            array('http://www.example.com/', array('foo_nothing', 'foo_domain')),
            array('http://foo.example.com/', array('foo_nothing', 'foo_domain')),
            array('http://foo.example1.com/', array('foo_nothing')),
            array('https://foo.example.com/', array('foo_nothing', 'foo_secure', 'foo_domain')),
            array('http://www.example.com/foo/bar', array('foo_nothing', 'foo_path', 'foo_domain')),
            array('http://www4.example.com/', array('foo_nothing', 'foo_domain', 'foo_strict_domain')),
        );
    }

    public function testEncodedValues()
    {
        $cookieJar = new Symfony_Component_BrowserKit_CookieJar();
        $cookieJar->set($cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar%3Dbaz', null, '/', '', false, true, true));

        $this->assertEquals(array('foo' => 'bar=baz'), $cookieJar->allValues('/'));
        $this->assertEquals(array('foo' => 'bar%3Dbaz'), $cookieJar->allRawValues('/'));
    }

    public function testCookieExpireWithSameNameButDifferentPaths()
    {
        $cookieJar = new Symfony_Component_BrowserKit_CookieJar();
        $cookieJar->set($cookie1 = new Symfony_Component_BrowserKit_Cookie('foo', 'bar1', null, '/foo'));
        $cookieJar->set($cookie2 = new Symfony_Component_BrowserKit_Cookie('foo', 'bar2', null, '/bar'));
        $cookieJar->expire('foo', '/foo');

        $this->assertNull($cookieJar->get('foo'), '->get() returns null if the cookie is expired');
        $this->assertEquals(array(), array_keys($cookieJar->allValues('http://example.com/')));
        $this->assertEquals(array(), $cookieJar->allValues('http://example.com/foo'));
        $this->assertEquals(array('foo' => 'bar2'), $cookieJar->allValues('http://example.com/bar'));
    }

    public function testCookieExpireWithNullPaths()
    {
        $cookieJar = new Symfony_Component_BrowserKit_CookieJar();
        $cookieJar->set($cookie1 = new Symfony_Component_BrowserKit_Cookie('foo', 'bar1', null, '/'));
        $cookieJar->expire('foo', null);

        $this->assertNull($cookieJar->get('foo'), '->get() returns null if the cookie is expired');
        $this->assertEquals(array(), array_keys($cookieJar->allValues('http://example.com/')));
    }

    public function testCookieWithSameNameButDifferentPaths()
    {
        $cookieJar = new Symfony_Component_BrowserKit_CookieJar();
        $cookieJar->set($cookie1 = new Symfony_Component_BrowserKit_Cookie('foo', 'bar1', null, '/foo'));
        $cookieJar->set($cookie2 = new Symfony_Component_BrowserKit_Cookie('foo', 'bar2', null, '/bar'));

        $this->assertEquals(array(), array_keys($cookieJar->allValues('http://example.com/')));
        $this->assertEquals(array('foo' => 'bar1'), $cookieJar->allValues('http://example.com/foo'));
        $this->assertEquals(array('foo' => 'bar2'), $cookieJar->allValues('http://example.com/bar'));
    }

    public function testCookieWithSameNameButDifferentDomains()
    {
        $cookieJar = new Symfony_Component_BrowserKit_CookieJar();
        $cookieJar->set($cookie1 = new Symfony_Component_BrowserKit_Cookie('foo', 'bar1', null, '/', 'foo.example.com'));
        $cookieJar->set($cookie2 = new Symfony_Component_BrowserKit_Cookie('foo', 'bar2', null, '/', 'bar.example.com'));

        $this->assertEquals(array(), array_keys($cookieJar->allValues('http://example.com/')));
        $this->assertEquals(array('foo' => 'bar1'), $cookieJar->allValues('http://foo.example.com/'));
        $this->assertEquals(array('foo' => 'bar2'), $cookieJar->allValues('http://bar.example.com/'));
    }
}
