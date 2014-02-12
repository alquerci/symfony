<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Http_EntryPoint_BasicAuthenticationEntryPointTest extends PHPUnit_Framework_TestCase
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

        $authException = new Symfony_Component_Security_Core_Exception_AuthenticationException('The exception message');

        $entryPoint = new Symfony_Component_Security_Http_EntryPoint_BasicAuthenticationEntryPoint('TheRealmName');
        $response = $entryPoint->start($request, $authException);

        $this->assertEquals('Basic realm="TheRealmName"', $response->headers->get('WWW-Authenticate'));
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testStartWithoutAuthException()
    {
        $request = $this->getMock('Symfony_Component_HttpFoundation_Request');

        $entryPoint = new Symfony_Component_Security_Http_EntryPoint_BasicAuthenticationEntryPoint('TheRealmName');

        $response = $entryPoint->start($request);

        $this->assertEquals('Basic realm="TheRealmName"', $response->headers->get('WWW-Authenticate'));
        $this->assertEquals(401, $response->getStatusCode());
    }
}
