<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Routing_Tests_Matcher_RedirectableUrlMatcherTest extends PHPUnit_Framework_TestCase
{
    public function testRedirectWhenNoSlash()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo', new Symfony_Component_Routing_Route('/foo/'));

        $matcher = $this->getMockForAbstractClass('Symfony_Component_Routing_Matcher_RedirectableUrlMatcher', array($coll, new Symfony_Component_Routing_RequestContext()));
        $matcher->expects($this->once())->method('redirect');
        $matcher->match('/foo');
    }

    /**
     * @expectedException Symfony_Component_Routing_Exception_ResourceNotFoundException
     */
    public function testRedirectWhenNoSlashForNonSafeMethod()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo', new Symfony_Component_Routing_Route('/foo/'));

        $context = new Symfony_Component_Routing_RequestContext();
        $context->setMethod('POST');
        $matcher = $this->getMockForAbstractClass('Symfony_Component_Routing_Matcher_RedirectableUrlMatcher', array($coll, $context));
        $matcher->match('/foo');
    }

    public function testSchemeRedirect()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo', new Symfony_Component_Routing_Route('/foo', array(), array('_scheme' => 'https')));

        $matcher = $this->getMockForAbstractClass('Symfony_Component_Routing_Matcher_RedirectableUrlMatcher', array($coll, new Symfony_Component_Routing_RequestContext()));
        $matcher
            ->expects($this->once())
            ->method('redirect')
            ->with('/foo', 'foo', 'https')
            ->will($this->returnValue(array('_route' => 'foo')))
        ;
        $matcher->match('/foo');
    }
}
