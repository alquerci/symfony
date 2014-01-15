<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_BrowserKit_Tests_CookieTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestsForToFromString
     */
    public function testToFromString($cookie, $url = null)
    {
        $this->assertEquals($cookie, (string) Symfony_Component_BrowserKit_Cookie::fromString($cookie, $url)->__toString());
    }

    public function getTestsForToFromString()
    {
        return array(
            array('foo=bar'),
            array('foo=bar; path=/foo'),
            array('foo=bar; domain=google.com'),
            array('foo=bar; domain=example.com; secure', 'https://example.com/'),
            array('foo=bar; httponly'),
            array('foo=bar; domain=google.com; path=/foo; secure; httponly', 'https://google.com/'),
            array('foo=bar=baz'),
            array('foo=bar%3Dbaz'),
        );
    }

    public function testFromStringIgnoreSecureFlag()
    {
        $this->assertFalse(Symfony_Component_BrowserKit_Cookie::fromString('foo=bar; secure')->isSecure());
        $this->assertFalse(Symfony_Component_BrowserKit_Cookie::fromString('foo=bar; secure', 'http://example.com/')->isSecure());
    }

    /**
     * @dataProvider getExpireCookieStrings
     */
    public function testFromStringAcceptsSeveralExpiresDateFormats($cookie)
    {
        $this->assertEquals(1596185377, Symfony_Component_BrowserKit_Cookie::fromString($cookie)->getExpiresTime());
    }

    public function getExpireCookieStrings()
    {
        return array(
            array('foo=bar; expires=Fri, 31-Jul-2020 08:49:37 GMT'),
            array('foo=bar; expires=Fri, 31 Jul 2020 08:49:37 GMT'),
            array('foo=bar; expires=Fri, 31-07-2020 08:49:37 GMT'),
            array('foo=bar; expires=Fri, 31-07-20 08:49:37 GMT'),
            array('foo=bar; expires=Friday, 31-Jul-20 08:49:37 GMT'),
            array('foo=bar; expires=Fri Jul 31 08:49:37 2020'),
            array('foo=bar; expires=\'Fri Jul 31 08:49:37 2020\''),
            array('foo=bar; expires=Friday July 31st 2020, 08:49:37 GMT'),
        );
    }

    public function testFromStringWithCapitalization()
    {
        $this->assertEquals('Foo=Bar', (string) Symfony_Component_BrowserKit_Cookie::fromString('Foo=Bar')->__toString());
        $this->assertEquals('foo=bar; expires=Fri, 31 Dec 2010 23:59:59 GMT', (string) Symfony_Component_BrowserKit_Cookie::fromString('foo=bar; Expires=Fri, 31 Dec 2010 23:59:59 GMT')->__toString());
        $this->assertEquals('foo=bar; domain=www.example.org; httponly', (string) Symfony_Component_BrowserKit_Cookie::fromString('foo=bar; DOMAIN=www.example.org; HttpOnly')->__toString());
    }

    public function testFromStringWithUrl()
    {
        $this->assertEquals('foo=bar; domain=www.example.com', (string) Symfony_Component_BrowserKit_Cookie::FromString('foo=bar', 'http://www.example.com/')->__toString());
        $this->assertEquals('foo=bar; domain=www.example.com; path=/foo', (string) Symfony_Component_BrowserKit_Cookie::FromString('foo=bar', 'http://www.example.com/foo/bar')->__toString());
        $this->assertEquals('foo=bar; domain=www.example.com', (string) Symfony_Component_BrowserKit_Cookie::FromString('foo=bar; path=/', 'http://www.example.com/foo/bar')->__toString());
        $this->assertEquals('foo=bar; domain=www.myotherexample.com', (string) Symfony_Component_BrowserKit_Cookie::FromString('foo=bar; domain=www.myotherexample.com', 'http://www.example.com/')->__toString());
    }

    public function testFromStringThrowsAnExceptionIfCookieIsNotValid()
    {
        $this->setExpectedException('InvalidArgumentException');
        Symfony_Component_BrowserKit_Cookie::FromString('foo');
    }

    public function testFromStringThrowsAnExceptionIfCookieDateIsNotValid()
    {
        $this->setExpectedException('InvalidArgumentException');
        Symfony_Component_BrowserKit_Cookie::FromString('foo=bar; expires=Flursday July 31st 2020, 08:49:37 GMT');
    }

    public function testFromStringThrowsAnExceptionIfUrlIsNotValid()
    {
        $this->setExpectedException('InvalidArgumentException');
        Symfony_Component_BrowserKit_Cookie::FromString('foo=bar', 'foobar');
    }

    public function testGetName()
    {
        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar');
        $this->assertEquals('foo', $cookie->getName(), '->getName() returns the cookie name');
    }

    public function testGetValue()
    {
        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar');
        $this->assertEquals('bar', $cookie->getValue(), '->getValue() returns the cookie value');

        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar%3Dbaz', null, '/', '', false, true, true); // raw value
        $this->assertEquals('bar=baz', $cookie->getValue(), '->getValue() returns the urldecoded cookie value');
    }

    public function testGetRawValue()
    {
        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar=baz'); // decoded value
        $this->assertEquals('bar%3Dbaz', $cookie->getRawValue(), '->getRawValue() returns the urlencoded cookie value');
        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar%3Dbaz', null, '/', '', false, true, true); // raw value
        $this->assertEquals('bar%3Dbaz', $cookie->getRawValue(), '->getRawValue() returns the non-urldecoded cookie value');
    }

    public function testGetPath()
    {
        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar', 0);
        $this->assertEquals('/', $cookie->getPath(), '->getPath() returns / is no path is defined');

        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar', 0, '/foo');
        $this->assertEquals('/foo', $cookie->getPath(), '->getPath() returns the cookie path');
    }

    public function testGetDomain()
    {
        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar', 0, '/', 'foo.com');
        $this->assertEquals('foo.com', $cookie->getDomain(), '->getDomain() returns the cookie domain');
    }

    public function testIsSecure()
    {
        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar');
        $this->assertFalse($cookie->isSecure(), '->isSecure() returns false if not defined');

        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar', 0, '/', 'foo.com', true);
        $this->assertTrue($cookie->isSecure(), '->isSecure() returns the cookie secure flag');
    }

    public function testIsHttponly()
    {
        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar');
        $this->assertTrue($cookie->isHttpOnly(), '->isHttpOnly() returns false if not defined');

        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar', 0, '/', 'foo.com', false, true);
        $this->assertTrue($cookie->isHttpOnly(), '->isHttpOnly() returns the cookie httponly flag');
    }

    public function testGetExpiresTime()
    {
        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar');
        $this->assertNull($cookie->getExpiresTime(), '->getExpiresTime() returns the expires time');

        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar', $time = time() - 86400);
        $this->assertEquals($time, $cookie->getExpiresTime(), '->getExpiresTime() returns the expires time');
    }

    public function testIsExpired()
    {
        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar');
        $this->assertFalse($cookie->isExpired(), '->isExpired() returns false when the cookie never expires (null as expires time)');

        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar', time() - 86400);
        $this->assertTrue($cookie->isExpired(), '->isExpired() returns true when the cookie is expired');

        $cookie = new Symfony_Component_BrowserKit_Cookie('foo', 'bar', 0);
        $this->assertFalse($cookie->isExpired());
    }
}
