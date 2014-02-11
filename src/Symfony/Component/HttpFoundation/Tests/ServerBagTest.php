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
 * ServerBagTest
 *
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class Symfony_Component_HttpFoundation_Tests_ServerBagTest extends PHPUnit_Framework_TestCase
{
    public function testShouldExtractHeadersFromServerArray()
    {
        $server = array(
            'SOME_SERVER_VARIABLE' => 'value',
            'SOME_SERVER_VARIABLE2' => 'value',
            'ROOT' => 'value',
            'HTTP_CONTENT_TYPE' => 'text/html',
            'HTTP_CONTENT_LENGTH' => '0',
            'HTTP_ETAG' => 'asdf',
            'PHP_AUTH_USER' => 'foo',
            'PHP_AUTH_PW' => 'bar',
        );

        $bag = new Symfony_Component_HttpFoundation_ServerBag($server);

        $this->assertEquals(array(
            'CONTENT_TYPE' => 'text/html',
            'CONTENT_LENGTH' => '0',
            'ETAG' => 'asdf',
            'AUTHORIZATION' => 'Basic '.base64_encode('foo:bar'),
            'PHP_AUTH_USER' => 'foo',
            'PHP_AUTH_PW' => 'bar',
        ), $bag->getHeaders());
    }

    public function testHttpPasswordIsOptional()
    {
        $bag = new Symfony_Component_HttpFoundation_ServerBag(array('PHP_AUTH_USER' => 'foo'));

        $this->assertEquals(array(
            'AUTHORIZATION' => 'Basic '.base64_encode('foo:'),
            'PHP_AUTH_USER' => 'foo',
            'PHP_AUTH_PW' => ''
        ), $bag->getHeaders());
    }

    public function testHttpBasicAuthWithPhpCgi()
    {
        $bag = new Symfony_Component_HttpFoundation_ServerBag(array('HTTP_AUTHORIZATION' => 'Basic '.base64_encode('foo:bar')));

        $this->assertEquals(array(
            'AUTHORIZATION' => 'Basic '.base64_encode('foo:bar'),
            'PHP_AUTH_USER' => 'foo',
            'PHP_AUTH_PW' => 'bar'
        ), $bag->getHeaders());
    }

    public function testHttpBasicAuthWithPhpCgiRedirect()
    {
        $bag = new Symfony_Component_HttpFoundation_ServerBag(array('REDIRECT_HTTP_AUTHORIZATION' => 'Basic '.base64_encode('foo:bar')));

        $this->assertEquals(array(
            'AUTHORIZATION' => 'Basic '.base64_encode('foo:bar'),
            'PHP_AUTH_USER' => 'foo',
            'PHP_AUTH_PW' => 'bar'
        ), $bag->getHeaders());
    }

    public function testHttpBasicAuthWithPhpCgiEmptyPassword()
    {
        $bag = new Symfony_Component_HttpFoundation_ServerBag(array('HTTP_AUTHORIZATION' => 'Basic '.base64_encode('foo:')));

        $this->assertEquals(array(
            'AUTHORIZATION' => 'Basic '.base64_encode('foo:'),
            'PHP_AUTH_USER' => 'foo',
            'PHP_AUTH_PW' => ''
        ), $bag->getHeaders());
    }

    public function testOAuthBearerAuth()
    {
        $headerContent = 'Bearer L-yLEOr9zhmUYRkzN1jwwxwQ-PBNiKDc8dgfB4hTfvo';
        $bag = new Symfony_Component_HttpFoundation_ServerBag(array('HTTP_AUTHORIZATION' => $headerContent));

        $this->assertEquals(array(
            'AUTHORIZATION' => $headerContent,
        ), $bag->getHeaders());
    }
}
