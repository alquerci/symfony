<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DomCrawler_Tests_LinkTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException LogicException
     */
    public function testConstructorWithANonATag()
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<html><div><div></html>');

        new Symfony_Component_DomCrawler_Link($dom->getElementsByTagName('div')->item(0), 'http://www.example.com/');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorWithAnInvalidCurrentUri()
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<html><a href="/foo">foo</a></html>');

        new Symfony_Component_DomCrawler_Link($dom->getElementsByTagName('a')->item(0), 'example.com');
    }

    public function testGetNode()
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<html><a href="/foo">foo</a></html>');

        $node = $dom->getElementsByTagName('a')->item(0);
        $link = new Symfony_Component_DomCrawler_Link($node, 'http://example.com/');

        $this->assertEquals($node, $link->getNode(), '->getNode() returns the node associated with the link');
    }

    public function testGetMethod()
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<html><a href="/foo">foo</a></html>');

        $node = $dom->getElementsByTagName('a')->item(0);
        $link = new Symfony_Component_DomCrawler_Link($node, 'http://example.com/');

        $this->assertEquals('GET', $link->getMethod(), '->getMethod() returns the method of the link');

        $link = new Symfony_Component_DomCrawler_Link($node, 'http://example.com/', 'post');
        $this->assertEquals('POST', $link->getMethod(), '->getMethod() returns the method of the link');
    }

    /**
     * @dataProvider getGetUriTests
     */
    public function testGetUri($url, $currentUri, $expected)
    {
        $dom = new DOMDocument();
        $dom->loadHTML(sprintf('<html><a href="%s">foo</a></html>', $url));
        $link = new Symfony_Component_DomCrawler_Link($dom->getElementsByTagName('a')->item(0), $currentUri);

        $this->assertEquals($expected, $link->getUri());
    }

    public function getGetUriTests()
    {
        return array(
            array('/foo', 'http://localhost/bar/foo/', 'http://localhost/foo'),
            array('/foo', 'http://localhost/bar/foo', 'http://localhost/foo'),
            array('
            /foo', 'http://localhost/bar/foo/', 'http://localhost/foo'),
            array('/foo
            ', 'http://localhost/bar/foo', 'http://localhost/foo'),

            array('foo', 'http://localhost/bar/foo/', 'http://localhost/bar/foo/foo'),
            array('foo', 'http://localhost/bar/foo', 'http://localhost/bar/foo'),

            array('', 'http://localhost/bar/', 'http://localhost/bar/'),
            array('#', 'http://localhost/bar/', 'http://localhost/bar/#'),
            array('#bar', 'http://localhost/bar/#foo', 'http://localhost/bar/#bar'),
            array('?a=b', 'http://localhost/bar/', 'http://localhost/bar/?a=b'),

            array('http://login.foo.com/foo', 'http://localhost/bar/', 'http://login.foo.com/foo'),
            array('https://login.foo.com/foo', 'https://localhost/bar/', 'https://login.foo.com/foo'),
            array('mailto:foo@bar.com', 'http://localhost/foo', 'mailto:foo@bar.com'),

            array('?foo=2', 'http://localhost?foo=1', 'http://localhost?foo=2'),
            array('?foo=2', 'http://localhost/?foo=1', 'http://localhost/?foo=2'),
            array('?foo=2', 'http://localhost/bar?foo=1', 'http://localhost/bar?foo=2'),
            array('?foo=2', 'http://localhost/bar/?foo=1', 'http://localhost/bar/?foo=2'),
            array('?bar=2', 'http://localhost?foo=1', 'http://localhost?bar=2'),

            array('foo', 'http://login.foo.com/bar/baz?/query/string', 'http://login.foo.com/bar/foo'),

            array('.', 'http://localhost/foo/bar/baz', 'http://localhost/foo/bar/'),
            array('./', 'http://localhost/foo/bar/baz', 'http://localhost/foo/bar/'),
            array('./foo', 'http://localhost/foo/bar/baz', 'http://localhost/foo/bar/foo'),
            array('..', 'http://localhost/foo/bar/baz', 'http://localhost/foo/'),
            array('../', 'http://localhost/foo/bar/baz', 'http://localhost/foo/'),
            array('../foo', 'http://localhost/foo/bar/baz', 'http://localhost/foo/foo'),
            array('../..', 'http://localhost/foo/bar/baz', 'http://localhost/'),
            array('../../', 'http://localhost/foo/bar/baz', 'http://localhost/'),
            array('../../foo', 'http://localhost/foo/bar/baz', 'http://localhost/foo'),
            array('../../foo', 'http://localhost/bar/foo/', 'http://localhost/foo'),
            array('../bar/../../foo', 'http://localhost/bar/foo/', 'http://localhost/foo'),
            array('../bar/./../../foo', 'http://localhost/bar/foo/', 'http://localhost/foo'),
            array('../../', 'http://localhost/', 'http://localhost/'),
            array('../../', 'http://localhost', 'http://localhost/'),
        );
    }
}
