<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Http_EntryPoint_DigestAuthenticationEntryPointTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }
    }

    public function testStart()
    {
        $request = $this->getMock('Symfony_Component_HttpFoundation_Request');

        $authenticationException = new Symfony_Component_Security_Core_Exception_AuthenticationException('TheAuthenticationExceptionMessage');

        $entryPoint = new Symfony_Component_Security_Http_EntryPoint_DigestAuthenticationEntryPoint('TheRealmName', 'TheKey');
        $response = $entryPoint->start($request, $authenticationException);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertRegExp('/^Digest realm="TheRealmName", qop="auth", nonce="[a-zA-Z0-9\/+]+={0,2}"$/', $response->headers->get('WWW-Authenticate'));
    }

    public function testStartWithNoException()
    {
        $request = $this->getMock('Symfony_Component_HttpFoundation_Request');

        $entryPoint = new Symfony_Component_Security_Http_EntryPoint_DigestAuthenticationEntryPoint('TheRealmName', 'TheKey');
        $response = $entryPoint->start($request);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertRegExp('/^Digest realm="TheRealmName", qop="auth", nonce="[a-zA-Z0-9\/+]+={0,2}"$/', $response->headers->get('WWW-Authenticate'));
    }

    public function testStartWithNonceExpiredException()
    {
        $request = $this->getMock('Symfony_Component_HttpFoundation_Request');

        $nonceExpiredException = new Symfony_Component_Security_Core_Exception_NonceExpiredException('TheNonceExpiredExceptionMessage');

        $entryPoint = new Symfony_Component_Security_Http_EntryPoint_DigestAuthenticationEntryPoint('TheRealmName', 'TheKey');
        $response = $entryPoint->start($request, $nonceExpiredException);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertRegExp('/^Digest realm="TheRealmName", qop="auth", nonce="[a-zA-Z0-9\/+]+={0,2}", stale="true"$/', $response->headers->get('WWW-Authenticate'));
    }
}
