<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Routing_Tests_RouteCollectionTest extends PHPUnit_Framework_TestCase
{
    public function testRoute()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $route = new Symfony_Component_Routing_Route('/foo');
        $collection->add('foo', $route);
        $this->assertEquals(array('foo' => $route), $collection->all(), '->add() adds a route');
        $this->assertEquals($route, $collection->get('foo'), '->get() returns a route by name');
        $this->assertNull($collection->get('bar'), '->get() returns null if a route does not exist');
    }

    public function testOverriddenRoute()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', new Symfony_Component_Routing_Route('/foo'));
        $collection->add('foo', new Symfony_Component_Routing_Route('/foo1'));

        $this->assertEquals('/foo1', $collection->get('foo')->getPath());
    }

    public function testDeepOverriddenRoute()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', new Symfony_Component_Routing_Route('/foo'));

        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->add('foo', new Symfony_Component_Routing_Route('/foo1'));

        $collection2 = new Symfony_Component_Routing_RouteCollection();
        $collection2->add('foo', new Symfony_Component_Routing_Route('/foo2'));

        $collection1->addCollection($collection2);
        $collection->addCollection($collection1);

        $this->assertEquals('/foo2', $collection1->get('foo')->getPath());
        $this->assertEquals('/foo2', $collection->get('foo')->getPath());
    }

    public function testIterator()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', new Symfony_Component_Routing_Route('/foo'));

        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->add('bar', $bar = new Symfony_Component_Routing_Route('/bar'));
        $collection1->add('foo', $foo = new Symfony_Component_Routing_Route('/foo-new'));
        $collection->addCollection($collection1);
        $collection->add('last', $last = new Symfony_Component_Routing_Route('/last'));

        $this->assertInstanceOf('ArrayIterator', $collection->getIterator());
        $this->assertSame(array('bar' => $bar, 'foo' => $foo, 'last' => $last), $collection->getIterator()->getArrayCopy());
    }

    public function testCount()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', new Symfony_Component_Routing_Route('/foo'));

        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->add('bar', new Symfony_Component_Routing_Route('/bar'));
        $collection->addCollection($collection1);

        $this->assertCount(2, $collection);
    }

    public function testAddCollection()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', new Symfony_Component_Routing_Route('/foo'));

        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->add('bar', $bar = new Symfony_Component_Routing_Route('/bar'));
        $collection1->add('foo', $foo = new Symfony_Component_Routing_Route('/foo-new'));

        $collection2 = new Symfony_Component_Routing_RouteCollection();
        $collection2->add('grandchild', $grandchild = new Symfony_Component_Routing_Route('/grandchild'));

        $collection1->addCollection($collection2);
        $collection->addCollection($collection1);
        $collection->add('last', $last = new Symfony_Component_Routing_Route('/last'));

        $this->assertSame(array('bar' => $bar, 'foo' => $foo, 'grandchild' => $grandchild, 'last' => $last), $collection->all(),
            '->addCollection() imports routes of another collection, overrides if necessary and adds them at the end');
    }

    public function testAddCollectionWithResources()
    {
        if (!class_exists('Symfony_Component_Config_Resource_FileResource')) {
            $this->markTestSkipped('The "Config" component is not available');
        }

        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->addResource($foo = new Symfony_Component_Config_Resource_FileResource(dirname(__FILE__).'/Fixtures/foo.xml'));
        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->addResource($foo1 = new Symfony_Component_Config_Resource_FileResource(dirname(__FILE__).'/Fixtures/foo1.xml'));
        $collection->addCollection($collection1);
        $this->assertEquals(array($foo, $foo1), $collection->getResources(), '->addCollection() merges resources');
    }

    public function testAddDefaultsAndRequirementsAndOptions()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', new Symfony_Component_Routing_Route('/{placeholder}'));
        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->add('bar', new Symfony_Component_Routing_Route('/{placeholder}',
            array('_controller' => 'fixed', 'placeholder' => 'default'), array('placeholder' => '.+'), array('option' => 'value'))
        );
        $collection->addCollection($collection1);

        $collection->addDefaults(array('placeholder' => 'new-default'));
        $this->assertEquals(array('placeholder' => 'new-default'), $collection->get('foo')->getDefaults(), '->addDefaults() adds defaults to all routes');
        $this->assertEquals(array('_controller' => 'fixed', 'placeholder' => 'new-default'), $collection->get('bar')->getDefaults(),
            '->addDefaults() adds defaults to all routes and overwrites existing ones');

        $collection->addRequirements(array('placeholder' => '\d+'));
        $this->assertEquals(array('placeholder' => '\d+'), $collection->get('foo')->getRequirements(), '->addRequirements() adds requirements to all routes');
        $this->assertEquals(array('placeholder' => '\d+'), $collection->get('bar')->getRequirements(),
            '->addRequirements() adds requirements to all routes and overwrites existing ones');

        $collection->addOptions(array('option' => 'new-value'));
        $this->assertEquals(
            array('option' => 'new-value', 'compiler_class' => 'Symfony_Component_Routing_RouteCompiler'),
            $collection->get('bar')->getOptions(), '->addOptions() adds options to all routes and overwrites existing ones'
        );
    }

    public function testAddPrefix()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', $foo = new Symfony_Component_Routing_Route('/foo'));
        $collection2 = new Symfony_Component_Routing_RouteCollection();
        $collection2->add('bar', $bar = new Symfony_Component_Routing_Route('/bar'));
        $collection->addCollection($collection2);
        $collection->addPrefix(' / ');
        $this->assertSame('/foo', $collection->get('foo')->getPattern(), '->addPrefix() trims the prefix and a single slash has no effect');
        $collection->addPrefix('/{admin}', array('admin' => 'admin'), array('admin' => '\d+'));
        $this->assertEquals('/{admin}/foo', $collection->get('foo')->getPath(), '->addPrefix() adds a prefix to all routes');
        $this->assertEquals('/{admin}/bar', $collection->get('bar')->getPath(), '->addPrefix() adds a prefix to all routes');
        $this->assertEquals(array('admin' => 'admin'), $collection->get('foo')->getDefaults(), '->addPrefix() adds defaults to all routes');
        $this->assertEquals(array('admin' => 'admin'), $collection->get('bar')->getDefaults(), '->addPrefix() adds defaults to all routes');
        $this->assertEquals(array('admin' => '\d+'), $collection->get('foo')->getRequirements(), '->addPrefix() adds requirements to all routes');
        $this->assertEquals(array('admin' => '\d+'), $collection->get('bar')->getRequirements(), '->addPrefix() adds requirements to all routes');
        $collection->addPrefix('0');
        $this->assertEquals('/0/{admin}/foo', $collection->get('foo')->getPattern(), '->addPrefix() ensures a prefix must start with a slash and must not end with a slash');
        $collection->addPrefix('/ /');
        $this->assertSame('/ /0/{admin}/foo', $collection->get('foo')->getPath(), '->addPrefix() can handle spaces if desired');
        $this->assertSame('/ /0/{admin}/bar', $collection->get('bar')->getPath(), 'the route pattern of an added collection is in synch with the added prefix');
    }

    public function testAddPrefixOverridesDefaultsAndRequirements()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', $foo = new Symfony_Component_Routing_Route('/foo'));
        $collection->add('bar', $bar = new Symfony_Component_Routing_Route('/bar', array(), array('_scheme' => 'http')));
        $collection->addPrefix('/admin', array(), array('_scheme' => 'https'));

        $this->assertEquals('https', $collection->get('foo')->getRequirement('_scheme'), '->addPrefix() overrides existing requirements');
        $this->assertEquals('https', $collection->get('bar')->getRequirement('_scheme'), '->addPrefix() overrides existing requirements');
    }

    public function testResource()
    {
        if (!class_exists('Symfony_Component_Config_Resource_FileResource')) {
            $this->markTestSkipped('The "Config" component is not available');
        }

        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->addResource($foo = new Symfony_Component_Config_Resource_FileResource(dirname(__FILE__).'/Fixtures/foo.xml'));
        $collection->addResource($bar = new Symfony_Component_Config_Resource_FileResource(dirname(__FILE__).'/Fixtures/bar.xml'));
        $collection->addResource(new Symfony_Component_Config_Resource_FileResource(dirname(__FILE__).'/Fixtures/foo.xml'));

        $this->assertEquals(array($foo, $bar), $collection->getResources(),
            '->addResource() adds a resource and getResources() only returns unique ones by comparing the string representation');
    }

    public function testUniqueRouteWithGivenName()
    {
        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->add('foo', new Symfony_Component_Routing_Route('/old'));
        $collection2 = new Symfony_Component_Routing_RouteCollection();
        $collection3 = new Symfony_Component_Routing_RouteCollection();
        $collection3->add('foo', $new = new Symfony_Component_Routing_Route('/new'));

        $collection2->addCollection($collection3);
        $collection1->addCollection($collection2);

        $this->assertSame($new, $collection1->get('foo'), '->get() returns new route that overrode previous one');
        // size of 1 because collection1 contains /new but not /old anymore
        $this->assertCount(1, $collection1->getIterator(), '->addCollection() removes previous routes when adding new routes with the same name');
    }

    public function testGet()
    {
        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->add('a', $a = new Symfony_Component_Routing_Route('/a'));
        $collection2 = new Symfony_Component_Routing_RouteCollection();
        $collection2->add('b', $b = new Symfony_Component_Routing_Route('/b'));
        $collection1->addCollection($collection2);
        $collection1->add('$péß^a|', $c = new Symfony_Component_Routing_Route('/special'));

        $this->assertSame($b, $collection1->get('b'), '->get() returns correct route in child collection');
        $this->assertSame($c, $collection1->get('$péß^a|'), '->get() can handle special characters');
        $this->assertNull($collection2->get('a'), '->get() does not return the route defined in parent collection');
        $this->assertNull($collection1->get('non-existent'), '->get() returns null when route does not exist');
        $this->assertNull($collection1->get(0), '->get() does not disclose internal child Symfony_Component_Routing_RouteCollection');
    }

    public function testRemove()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', $foo = new Symfony_Component_Routing_Route('/foo'));

        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->add('bar', $bar = new Symfony_Component_Routing_Route('/bar'));
        $collection->addCollection($collection1);
        $collection->add('last', $last = new Symfony_Component_Routing_Route('/last'));

        $collection->remove('foo');
        $this->assertSame(array('bar' => $bar, 'last' => $last), $collection->all(), '->remove() can remove a single route');
        $collection->remove(array('bar', 'last'));
        $this->assertSame(array(), $collection->all(), '->remove() accepts an array and can remove multiple routes at once');
    }

    public function testSetHost()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $routea = new Symfony_Component_Routing_Route('/a');
        $routeb = new Symfony_Component_Routing_Route('/b', array(), array(), array(), '{locale}.example.net');
        $collection->add('a', $routea);
        $collection->add('b', $routeb);

        $collection->setHost('{locale}.example.com');

        $this->assertEquals('{locale}.example.com', $routea->getHost());
        $this->assertEquals('{locale}.example.com', $routeb->getHost());
    }
}
