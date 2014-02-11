<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpFoundation_Tests_RedirectResponseTest extends PHPUnit_Framework_TestCase
{
    public function testGenerateMetaRedirect()
    {
        $response = new Symfony_Component_HttpFoundation_RedirectResponse('foo.bar');

        $this->assertEquals(1, preg_match(
            '#<meta http-equiv="refresh" content="\d+;url=foo\.bar" />#',
            preg_replace(array('/\s+/', '/\'/'), array(' ', '"'), $response->getContent())
        ));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRedirectResponseConstructorNullUrl()
    {
        $response = new Symfony_Component_HttpFoundation_RedirectResponse(null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRedirectResponseConstructorWrongStatusCode()
    {
        $response = new Symfony_Component_HttpFoundation_RedirectResponse('foo.bar', 404);
    }

    public function testGenerateLocationHeader()
    {
        $response = new Symfony_Component_HttpFoundation_RedirectResponse('foo.bar');

        $this->assertTrue($response->headers->has('Location'));
        $this->assertEquals('foo.bar', $response->headers->get('Location'));
    }

    public function testGetTargetUrl()
    {
        $response = new Symfony_Component_HttpFoundation_RedirectResponse('foo.bar');

        $this->assertEquals('foo.bar', $response->getTargetUrl());
    }

    public function testSetTargetUrl()
    {
        $response = new Symfony_Component_HttpFoundation_RedirectResponse('foo.bar');
        $response->setTargetUrl('baz.beep');

        $this->assertEquals('baz.beep', $response->getTargetUrl());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetTargetUrlNull()
    {
        $response = new Symfony_Component_HttpFoundation_RedirectResponse('foo.bar');
        $response->setTargetUrl(null);
    }

    public function testCreate()
    {
        $response = Symfony_Component_HttpFoundation_RedirectResponse::create('foo', 301);

        $this->assertInstanceOf('Symfony_Component_HttpFoundation_RedirectResponse', $response);
        $this->assertEquals(301, $response->getStatusCode());
    }
}
