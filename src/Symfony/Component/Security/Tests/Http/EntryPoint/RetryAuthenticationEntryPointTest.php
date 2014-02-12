<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Http_EntryPoint_RetryAuthenticationEntryPointTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }
    }

    /**
     * @dataProvider dataForStart
     */
    public function testStart($httpPort, $httpsPort, $request, $expectedUrl)
    {
        $entryPoint = new Symfony_Component_Security_Http_EntryPoint_RetryAuthenticationEntryPoint($httpPort, $httpsPort);
        $response = $entryPoint->start($request);

        $this->assertInstanceOf('Symfony_Component_HttpFoundation_RedirectResponse', $response);
        $this->assertEquals($expectedUrl, $response->headers->get('Location'));
    }

    public function dataForStart()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            return array(array());
        }

        return array(
            array(
                80,
                443,
                Symfony_Component_HttpFoundation_Request::create('http://localhost/foo/bar?baz=bat'),
                'https://localhost/foo/bar?baz=bat'
            ),
            array(
                80,
                443,
                Symfony_Component_HttpFoundation_Request::create('https://localhost/foo/bar?baz=bat'),
                'http://localhost/foo/bar?baz=bat'
            ),
            array(
                80,
                123,
                Symfony_Component_HttpFoundation_Request::create('http://localhost/foo/bar?baz=bat'),
                'https://localhost:123/foo/bar?baz=bat'
            ),
            array(
                8080,
                443,
                Symfony_Component_HttpFoundation_Request::create('https://localhost/foo/bar?baz=bat'),
                'http://localhost:8080/foo/bar?baz=bat'
            )
        );
    }
}
