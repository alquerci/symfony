<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Routing_RedirectableUrlMatcherTest extends PHPUnit_Framework_TestCase
{
    public function testRedirectWhenNoSlash()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo', new Symfony_Component_Routing_Route('/foo/'));

        $matcher = new Symfony_Bundle_FrameworkBundle_Routing_RedirectableUrlMatcher($coll, $context = new Symfony_Component_Routing_RequestContext());

        $this->assertEquals(array(
                '_controller' => 'Symfony_Bundle_FrameworkBundle_Controller_RedirectController::urlRedirectAction',
                'path'        => '/foo/',
                'permanent'   => true,
                'scheme'      => null,
                'httpPort'    => $context->getHttpPort(),
                'httpsPort'   => $context->getHttpsPort(),
                '_route'      => null,
            ),
            $matcher->match('/foo')
        );
    }

    public function testSchemeRedirect()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo', new Symfony_Component_Routing_Route('/foo', array(), array('_scheme' => 'https')));

        $matcher = new Symfony_Bundle_FrameworkBundle_Routing_RedirectableUrlMatcher($coll, $context = new Symfony_Component_Routing_RequestContext());

        $this->assertEquals(array(
                '_controller' => 'Symfony_Bundle_FrameworkBundle_Controller_RedirectController::urlRedirectAction',
                'path'        => '/foo',
                'permanent'   => true,
                'scheme'      => 'https',
                'httpPort'    => $context->getHttpPort(),
                'httpsPort'   => $context->getHttpsPort(),
                '_route'      => 'foo',
            ),
            $matcher->match('/foo')
        );
    }
}
