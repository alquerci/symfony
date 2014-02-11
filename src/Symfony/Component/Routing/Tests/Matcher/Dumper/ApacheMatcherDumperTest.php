<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Routing_Tests_Matcher_Dumper_ApacheMatcherDumperTest extends PHPUnit_Framework_TestCase
{
    protected static $fixturesPath;

    public static function setUpBeforeClass()
    {
        self::$fixturesPath = realpath(dirname(__FILE__).'/../../Fixtures/');
    }

    public function testDump()
    {
        $dumper = new Symfony_Component_Routing_Matcher_Dumper_ApacheMatcherDumper($this->getRouteCollection());

        $this->assertStringEqualsFile(self::$fixturesPath.'/dumper/url_matcher1.apache', $dumper->dump(), '->dump() dumps basic routes to the correct apache format.');
    }

    /**
     * @dataProvider provideEscapeFixtures
     */
    public function testEscapePattern($src, $dest, $char, $with, $message)
    {
        if (version_compare(PHP_VERSION, '5.3.2', '<')) {
            $this->markTestIncomplete('Require ReflectionMethod::setAccessible');
        }

        $r = new ReflectionMethod(new Symfony_Component_Routing_Matcher_Dumper_ApacheMatcherDumper($this->getRouteCollection()), 'escape');
        $r->setAccessible(true);
        $this->assertEquals($dest, $r->invoke(null, $src, $char, $with), $message);
    }

    public function provideEscapeFixtures()
    {
        return array(
            array('foo', 'foo', ' ', '-', 'Preserve string that should not be escaped'),
            array('fo-o', 'fo-o', ' ', '-', 'Preserve string that should not be escaped'),
            array('fo o', 'fo- o', ' ', '-', 'Escape special characters'),
            array('fo-- o', 'fo--- o', ' ', '-', 'Escape special characters'),
            array('fo- o', 'fo- o', ' ', '-', 'Do not escape already escaped string'),
        );
    }

    public function testEscapeScriptName()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', new Symfony_Component_Routing_Route('/foo'));
        $dumper = new Symfony_Component_Routing_Matcher_Dumper_ApacheMatcherDumper($collection);
        $this->assertStringEqualsFile(self::$fixturesPath.'/dumper/url_matcher2.apache', $dumper->dump(array('script_name' => 'ap p_d\ ev.php')));
    }

    private function getRouteCollection()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();

        // defaults and requirements
        $collection->add('foo', new Symfony_Component_Routing_Route(
            '/foo/{bar}',
            array('def' => 'test'),
            array('bar' => 'baz|symfony')
        ));
        // defaults parameters in pattern
        $collection->add('foobar', new Symfony_Component_Routing_Route(
            '/foo/{bar}',
            array('bar' => 'toto')
        ));
        // method requirement
        $collection->add('bar', new Symfony_Component_Routing_Route(
            '/bar/{foo}',
            array(),
            array('_method' => 'GET|head')
        ));
        // method requirement (again)
        $collection->add('baragain', new Symfony_Component_Routing_Route(
            '/baragain/{foo}',
            array(),
            array('_method' => 'get|post')
        ));
        // simple
        $collection->add('baz', new Symfony_Component_Routing_Route(
            '/test/baz'
        ));
        // simple with extension
        $collection->add('baz2', new Symfony_Component_Routing_Route(
            '/test/baz.html'
        ));
        // trailing slash
        $collection->add('baz3', new Symfony_Component_Routing_Route(
            '/test/baz3/'
        ));
        // trailing slash with variable
        $collection->add('baz4', new Symfony_Component_Routing_Route(
            '/test/{foo}/'
        ));
        // trailing slash and safe method
        $collection->add('baz5', new Symfony_Component_Routing_Route(
            '/test/{foo}/',
            array(),
            array('_method' => 'get')
        ));
        // trailing slash and unsafe method
        $collection->add('baz5unsafe', new Symfony_Component_Routing_Route(
            '/testunsafe/{foo}/',
            array(),
            array('_method' => 'post')
        ));
        // complex
        $collection->add('baz6', new Symfony_Component_Routing_Route(
            '/test/baz',
            array('foo' => 'bar baz')
        ));
        // space in path
        $collection->add('baz7', new Symfony_Component_Routing_Route(
            '/te st/baz'
        ));
        // space preceded with \ in path
        $collection->add('baz8', new Symfony_Component_Routing_Route(
            '/te\\ st/baz'
        ));
        // space preceded with \ in requirement
        $collection->add('baz9', new Symfony_Component_Routing_Route(
            '/test/{baz}',
            array(),
            array(
                'baz' => 'te\\\\ st',
            )
        ));

        $collection1 = new Symfony_Component_Routing_RouteCollection();

        $route1 = new Symfony_Component_Routing_Route('/route1', array(), array(), array(), 'a.example.com');
        $collection1->add('route1', $route1);

        $collection2 = new Symfony_Component_Routing_RouteCollection();

        $route2 = new Symfony_Component_Routing_Route('/route2', array(), array(), array(), 'a.example.com');
        $collection2->add('route2', $route2);

        $route3 = new Symfony_Component_Routing_Route('/route3', array(), array(), array(), 'b.example.com');
        $collection2->add('route3', $route3);

        $collection2->addPrefix('/c2');
        $collection1->addCollection($collection2);

        $route4 = new Symfony_Component_Routing_Route('/route4', array(), array(), array(), 'a.example.com');
        $collection1->add('route4', $route4);

        $route5 = new Symfony_Component_Routing_Route('/route5', array(), array(), array(), 'c.example.com');
        $collection1->add('route5', $route5);

        $route6 = new Symfony_Component_Routing_Route('/route6', array(), array(), array(), null);
        $collection1->add('route6', $route6);

        $collection->addCollection($collection1);

        // host and variables

        $collection1 = new Symfony_Component_Routing_RouteCollection();

        $route11 = new Symfony_Component_Routing_Route('/route11', array(), array(), array(), '{var1}.example.com');
        $collection1->add('route11', $route11);

        $route12 = new Symfony_Component_Routing_Route('/route12', array('var1' => 'val'), array(), array(), '{var1}.example.com');
        $collection1->add('route12', $route12);

        $route13 = new Symfony_Component_Routing_Route('/route13/{name}', array(), array(), array(), '{var1}.example.com');
        $collection1->add('route13', $route13);

        $route14 = new Symfony_Component_Routing_Route('/route14/{name}', array('var1' => 'val'), array(), array(), '{var1}.example.com');
        $collection1->add('route14', $route14);

        $route15 = new Symfony_Component_Routing_Route('/route15/{name}', array(), array(), array(), 'c.example.com');
        $collection1->add('route15', $route15);

        $route16 = new Symfony_Component_Routing_Route('/route16/{name}', array('var1' => 'val'), array(), array(), null);
        $collection1->add('route16', $route16);

        $route17 = new Symfony_Component_Routing_Route('/route17', array(), array(), array(), null);
        $collection1->add('route17', $route17);

        $collection->addCollection($collection1);

        return $collection;
    }
}
