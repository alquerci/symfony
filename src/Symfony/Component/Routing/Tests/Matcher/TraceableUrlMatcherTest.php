<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Routing_Tests_Matcher_TraceableUrlMatcherTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo', new Symfony_Component_Routing_Route('/foo', array(), array('_method' => 'POST')));
        $coll->add('bar', new Symfony_Component_Routing_Route('/bar/{id}', array(), array('id' => '\d+')));
        $coll->add('bar1', new Symfony_Component_Routing_Route('/bar/{name}', array(), array('id' => '\w+', '_method' => 'POST')));
        $coll->add('bar2', new Symfony_Component_Routing_Route('/foo', array(), array(), array(), 'baz'));
        $coll->add('bar3', new Symfony_Component_Routing_Route('/foo1', array(), array(), array(), 'baz'));

        $context = new Symfony_Component_Routing_RequestContext();
        $context->setHost('baz');

        $matcher = new Symfony_Component_Routing_Matcher_TraceableUrlMatcher($coll, $context);
        $traces = $matcher->getTraces('/babar');
        $this->assertEquals(array(0, 0, 0, 0, 0), $this->getLevels($traces));

        $traces = $matcher->getTraces('/foo');
        $this->assertEquals(array(1, 0, 0, 2), $this->getLevels($traces));

        $traces = $matcher->getTraces('/bar/12');
        $this->assertEquals(array(0, 2), $this->getLevels($traces));

        $traces = $matcher->getTraces('/bar/dd');
        $this->assertEquals(array(0, 1, 1, 0, 0), $this->getLevels($traces));

        $traces = $matcher->getTraces('/foo1');
        $this->assertEquals(array(0, 0, 0, 0, 2), $this->getLevels($traces));

        $context->setMethod('POST');
        $traces = $matcher->getTraces('/foo');
        $this->assertEquals(array(2), $this->getLevels($traces));

        $traces = $matcher->getTraces('/bar/dd');
        $this->assertEquals(array(0, 1, 2), $this->getLevels($traces));
    }

    public function getLevels($traces)
    {
        $levels = array();
        foreach ($traces as $trace) {
            $levels[] = $trace['level'];
        }

        return $levels;
    }
}
