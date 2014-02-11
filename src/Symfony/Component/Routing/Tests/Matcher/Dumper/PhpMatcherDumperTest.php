<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Routing_Tests_Matcher_Dumper_PhpMatcherDumperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException LogicException
     */
    public function testDumpWhenSchemeIsUsedWithoutAProperDumper()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('secure', new Symfony_Component_Routing_Route(
            '/secure',
            array(),
            array('_scheme' => 'https')
        ));
        $dumper = new Symfony_Component_Routing_Matcher_Dumper_PhpMatcherDumper($collection);
        $dumper->dump();
    }

    /**
     * @dataProvider getRouteCollections
     */
    public function testDump(Symfony_Component_Routing_RouteCollection $collection, $fixture, $options = array())
    {
        $basePath = dirname(__FILE__).'/../../Fixtures/dumper/';

        $dumper = new Symfony_Component_Routing_Matcher_Dumper_PhpMatcherDumper($collection);
        $this->assertStringEqualsFile($basePath.$fixture, $dumper->dump($options), '->dump() correctly dumps routes as optimized PHP code.');
    }

    public function getRouteCollections()
    {
        /* test case 1 */

        $collection = new Symfony_Component_Routing_RouteCollection();

        $collection->add('overridden', new Symfony_Component_Routing_Route('/overridden'));

        // defaults and requirements
        $collection->add('foo', new Symfony_Component_Routing_Route(
            '/foo/{bar}',
            array('def' => 'test'),
            array('bar' => 'baz|symfony')
        ));
        // method requirement
        $collection->add('bar', new Symfony_Component_Routing_Route(
            '/bar/{foo}',
            array(),
            array('_method' => 'GET|head')
        ));
        // GET method requirement automatically adds HEAD as valid
        $collection->add('barhead', new Symfony_Component_Routing_Route(
            '/barhead/{foo}',
            array(),
            array('_method' => 'GET')
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
        // trailing slash and method
        $collection->add('baz5', new Symfony_Component_Routing_Route(
            '/test/{foo}/',
            array(),
            array('_method' => 'post')
        ));
        // complex name
        $collection->add('baz.baz6', new Symfony_Component_Routing_Route(
            '/test/{foo}/',
            array(),
            array('_method' => 'put')
        ));
        // defaults without variable
        $collection->add('foofoo', new Symfony_Component_Routing_Route(
            '/foofoo',
            array('def' => 'test')
        ));
        // pattern with quotes
        $collection->add('quoter', new Symfony_Component_Routing_Route(
            '/{quoter}',
            array(),
            array('quoter' => '[\']+')
        ));
        // space in pattern
        $collection->add('space', new Symfony_Component_Routing_Route(
            '/spa ce'
        ));

        // prefixes
        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->add('overridden', new Symfony_Component_Routing_Route('/overridden1'));
        $collection1->add('foo1', new Symfony_Component_Routing_Route('/{foo}'));
        $collection1->add('bar1', new Symfony_Component_Routing_Route('/{bar}'));
        $collection1->addPrefix('/b\'b');
        $collection2 = new Symfony_Component_Routing_RouteCollection();
        $collection2->addCollection($collection1);
        $collection2->add('overridden', new Symfony_Component_Routing_Route('/{var}', array(), array('var' => '.*')));
        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->add('foo2', new Symfony_Component_Routing_Route('/{foo1}'));
        $collection1->add('bar2', new Symfony_Component_Routing_Route('/{bar1}'));
        $collection1->addPrefix('/b\'b');
        $collection2->addCollection($collection1);
        $collection2->addPrefix('/a');
        $collection->addCollection($collection2);

        // overridden through addCollection() and multiple sub-collections with no own prefix
        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->add('overridden2', new Symfony_Component_Routing_Route('/old'));
        $collection1->add('helloWorld', new Symfony_Component_Routing_Route('/hello/{who}', array('who' => 'World!')));
        $collection2 = new Symfony_Component_Routing_RouteCollection();
        $collection3 = new Symfony_Component_Routing_RouteCollection();
        $collection3->add('overridden2', new Symfony_Component_Routing_Route('/new'));
        $collection3->add('hey', new Symfony_Component_Routing_Route('/hey/'));
        $collection2->addCollection($collection3);
        $collection1->addCollection($collection2);
        $collection1->addPrefix('/multi');
        $collection->addCollection($collection1);

        // "dynamic" prefix
        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->add('foo3', new Symfony_Component_Routing_Route('/{foo}'));
        $collection1->add('bar3', new Symfony_Component_Routing_Route('/{bar}'));
        $collection1->addPrefix('/b');
        $collection1->addPrefix('{_locale}');
        $collection->addCollection($collection1);

        // route between collections
        $collection->add('ababa', new Symfony_Component_Routing_Route('/ababa'));

        // collection with static prefix but only one route
        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->add('foo4', new Symfony_Component_Routing_Route('/{foo}'));
        $collection1->addPrefix('/aba');
        $collection->addCollection($collection1);

        // prefix and host

        $collection1 = new Symfony_Component_Routing_RouteCollection();

        $route1 = new Symfony_Component_Routing_Route('/route1', array(), array(), array(), 'a.example.com');
        $collection1->add('route1', $route1);

        $collection2 = new Symfony_Component_Routing_RouteCollection();

        $route2 = new Symfony_Component_Routing_Route('/c2/route2', array(), array(), array(), 'a.example.com');
        $collection1->add('route2', $route2);

        $route3 = new Symfony_Component_Routing_Route('/c2/route3', array(), array(), array(), 'b.example.com');
        $collection1->add('route3', $route3);

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

        // multiple sub-collections with a single route and a prefix each
        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->add('a', new Symfony_Component_Routing_Route('/a...'));
        $collection2 = new Symfony_Component_Routing_RouteCollection();
        $collection2->add('b', new Symfony_Component_Routing_Route('/{var}'));
        $collection3 = new Symfony_Component_Routing_RouteCollection();
        $collection3->add('c', new Symfony_Component_Routing_Route('/{var}'));
        $collection3->addPrefix('/c');
        $collection2->addCollection($collection3);
        $collection2->addPrefix('/b');
        $collection1->addCollection($collection2);
        $collection1->addPrefix('/a');
        $collection->addCollection($collection1);

        /* test case 2 */

        $redirectCollection = clone $collection;

        // force HTTPS redirection
        $redirectCollection->add('secure', new Symfony_Component_Routing_Route(
            '/secure',
            array(),
            array('_scheme' => 'https')
        ));

        // force HTTP redirection
        $redirectCollection->add('nonsecure', new Symfony_Component_Routing_Route(
            '/nonsecure',
            array(),
            array('_scheme' => 'http')
        ));

        /* test case 3 */

        $rootprefixCollection = new Symfony_Component_Routing_RouteCollection();
        $rootprefixCollection->add('static', new Symfony_Component_Routing_Route('/test'));
        $rootprefixCollection->add('dynamic', new Symfony_Component_Routing_Route('/{var}'));
        $rootprefixCollection->addPrefix('rootprefix');

        return array(
           array($collection, 'url_matcher1.php', array()),
           array($redirectCollection, 'url_matcher2.php', array('base_class' => 'Symfony_Component_Routing_Tests_Fixtures_RedirectableUrlMatcher')),
           array($rootprefixCollection, 'url_matcher3.php', array())
        );
    }
}
