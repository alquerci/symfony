<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Routing_Tests_Matcher_UrlMatcherTest extends PHPUnit_Framework_TestCase
{
    public function testNoMethodSoAllowed()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo', new Symfony_Component_Routing_Route('/foo'));

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext());
        $matcher->match('/foo');
    }

    public function testMethodNotAllowed()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo', new Symfony_Component_Routing_Route('/foo', array(), array('_method' => 'post')));

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext());

        try {
            $matcher->match('/foo');
            $this->fail();
        } catch (Symfony_Component_Routing_Exception_MethodNotAllowedException $e) {
            $this->assertEquals(array('POST'), $e->getAllowedMethods());
        }
    }

    public function testHeadAllowedWhenRequirementContainsGet()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo', new Symfony_Component_Routing_Route('/foo', array(), array('_method' => 'get')));

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext('', 'head'));
        $matcher->match('/foo');
    }

    public function testMethodNotAllowedAggregatesAllowedMethods()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo1', new Symfony_Component_Routing_Route('/foo', array(), array('_method' => 'post')));
        $coll->add('foo2', new Symfony_Component_Routing_Route('/foo', array(), array('_method' => 'put|delete')));

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext());

        try {
            $matcher->match('/foo');
            $this->fail();
        } catch (Symfony_Component_Routing_Exception_MethodNotAllowedException $e) {
            $this->assertEquals(array('POST', 'PUT', 'DELETE'), $e->getAllowedMethods());
        }
    }

    public function testMatch()
    {
        // test the patterns are matched and parameters are returned
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', new Symfony_Component_Routing_Route('/foo/{bar}'));
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext());
        try {
            $matcher->match('/no-match');
            $this->fail();
        } catch (Symfony_Component_Routing_Exception_ResourceNotFoundException $e) {}
        $this->assertEquals(array('_route' => 'foo', 'bar' => 'baz'), $matcher->match('/foo/baz'));

        // test that defaults are merged
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', new Symfony_Component_Routing_Route('/foo/{bar}', array('def' => 'test')));
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext());
        $this->assertEquals(array('_route' => 'foo', 'bar' => 'baz', 'def' => 'test'), $matcher->match('/foo/baz'));

        // test that route "method" is ignored if no method is given in the context
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', new Symfony_Component_Routing_Route('/foo', array(), array('_method' => 'GET|head')));
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext());
        $this->assertInternalType('array', $matcher->match('/foo'));

        // route does not match with POST method context
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext('', 'post'));
        try {
            $matcher->match('/foo');
            $this->fail();
        } catch (Symfony_Component_Routing_Exception_MethodNotAllowedException $e) {}

        // route does match with GET or HEAD method context
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext());
        $this->assertInternalType('array', $matcher->match('/foo'));
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext('', 'head'));
        $this->assertInternalType('array', $matcher->match('/foo'));

        // route with an optional variable as the first segment
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('bar', new Symfony_Component_Routing_Route('/{bar}/foo', array('bar' => 'bar'), array('bar' => 'foo|bar')));
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext());
        $this->assertEquals(array('_route' => 'bar', 'bar' => 'bar'), $matcher->match('/bar/foo'));
        $this->assertEquals(array('_route' => 'bar', 'bar' => 'foo'), $matcher->match('/foo/foo'));

        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('bar', new Symfony_Component_Routing_Route('/{bar}', array('bar' => 'bar'), array('bar' => 'foo|bar')));
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext());
        $this->assertEquals(array('_route' => 'bar', 'bar' => 'foo'), $matcher->match('/foo'));
        $this->assertEquals(array('_route' => 'bar', 'bar' => 'bar'), $matcher->match('/'));

        // route with only optional variables
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('bar', new Symfony_Component_Routing_Route('/{foo}/{bar}', array('foo' => 'foo', 'bar' => 'bar'), array()));
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext());
        $this->assertEquals(array('_route' => 'bar', 'foo' => 'foo', 'bar' => 'bar'), $matcher->match('/'));
        $this->assertEquals(array('_route' => 'bar', 'foo' => 'a', 'bar' => 'bar'), $matcher->match('/a'));
        $this->assertEquals(array('_route' => 'bar', 'foo' => 'a', 'bar' => 'b'), $matcher->match('/a/b'));
    }

    public function testMatchWithPrefixes()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', new Symfony_Component_Routing_Route('/{foo}'));
        $collection->addPrefix('/b');
        $collection->addPrefix('/a');

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext());
        $this->assertEquals(array('_route' => 'foo', 'foo' => 'foo'), $matcher->match('/a/b/foo'));
    }

    public function testMatchWithDynamicPrefix()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', new Symfony_Component_Routing_Route('/{foo}'));
        $collection->addPrefix('/b');
        $collection->addPrefix('/{_locale}');

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext());
        $this->assertEquals(array('_locale' => 'fr', '_route' => 'foo', 'foo' => 'foo'), $matcher->match('/fr/b/foo'));
    }

    public function testMatchSpecialRouteName()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('$péß^a|', new Symfony_Component_Routing_Route('/bar'));

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext());
        $this->assertEquals(array('_route' => '$péß^a|'), $matcher->match('/bar'));
    }

    public function testMatchNonAlpha()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $chars = '!"$%éà &\'()*+,./:;<=>@ABCDEFGHIJKLMNOPQRSTUVWXYZ\\[]^_`abcdefghijklmnopqrstuvwxyz{|}~-';
        $collection->add('foo', new Symfony_Component_Routing_Route('/{foo}/bar', array(), array('foo' => '['.preg_quote($chars).']+')));

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext());
        $this->assertEquals(array('_route' => 'foo', 'foo' => $chars), $matcher->match('/'.rawurlencode($chars).'/bar'));
        $this->assertEquals(array('_route' => 'foo', 'foo' => $chars), $matcher->match('/'.strtr($chars, array('%' => '%25')).'/bar'));
    }

    public function testMatchWithDotMetacharacterInRequirements()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', new Symfony_Component_Routing_Route('/{foo}/bar', array(), array('foo' => '.+')));

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext());
        $this->assertEquals(array('_route' => 'foo', 'foo' => "\n"), $matcher->match('/'.urlencode("\n").'/bar'), 'linefeed character is matched');
    }

    public function testMatchOverriddenRoute()
    {
        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', new Symfony_Component_Routing_Route('/foo'));

        $collection1 = new Symfony_Component_Routing_RouteCollection();
        $collection1->add('foo', new Symfony_Component_Routing_Route('/foo1'));

        $collection->addCollection($collection1);

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext());

        $this->assertEquals(array('_route' => 'foo'), $matcher->match('/foo1'));
        $this->setExpectedException('Symfony_Component_Routing_Exception_ResourceNotFoundException');
        $this->assertEquals(array(), $matcher->match('/foo'));
    }

    public function testMatchRegression()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo', new Symfony_Component_Routing_Route('/foo/{foo}'));
        $coll->add('bar', new Symfony_Component_Routing_Route('/foo/bar/{foo}'));

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext());
        $this->assertEquals(array('foo' => 'bar', '_route' => 'bar'), $matcher->match('/foo/bar/bar'));

        $collection = new Symfony_Component_Routing_RouteCollection();
        $collection->add('foo', new Symfony_Component_Routing_Route('/{bar}'));
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($collection, new Symfony_Component_Routing_RequestContext());
        try {
            $matcher->match('/');
            $this->fail();
        } catch (Symfony_Component_Routing_Exception_ResourceNotFoundException $e) {
        }
    }

    public function testDefaultRequirementForOptionalVariables()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('test', new Symfony_Component_Routing_Route('/{page}.{_format}', array('page' => 'index', '_format' => 'html')));

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext());
        $this->assertEquals(array('page' => 'my-page', '_format' => 'xml', '_route' => 'test'), $matcher->match('/my-page.xml'));
    }

    public function testMatchingIsEager()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('test', new Symfony_Component_Routing_Route('/{foo}-{bar}-', array(), array('foo' => '.+', 'bar' => '.+')));

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext());
        $this->assertEquals(array('foo' => 'text1-text2-text3', 'bar' => 'text4', '_route' => 'test'), $matcher->match('/text1-text2-text3-text4-'));
    }

    public function testAdjacentVariables()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('test', new Symfony_Component_Routing_Route('/{w}{x}{y}{z}.{_format}', array('z' => 'default-z', '_format' => 'html'), array('y' => 'y|Y')));

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext());
        // 'w' eagerly matches as much as possible and the other variables match the remaining chars.
        // This also shows that the variables w-z must all exclude the separating char (the dot '.' in this case) by default requirement.
        // Otherwise they would also consume '.xml' and _format would never match as it's an optional variable.
        $this->assertEquals(array('w' => 'wwwww', 'x' => 'x', 'y' => 'Y', 'z' => 'Z','_format' => 'xml', '_route' => 'test'), $matcher->match('/wwwwwxYZ.xml'));
        // As 'y' has custom requirement and can only be of value 'y|Y', it will leave  'ZZZ' to variable z.
        // So with carefully chosen requirements adjacent variables, can be useful.
        $this->assertEquals(array('w' => 'wwwww', 'x' => 'x', 'y' => 'y', 'z' => 'ZZZ','_format' => 'html', '_route' => 'test'), $matcher->match('/wwwwwxyZZZ'));
        // z and _format are optional.
        $this->assertEquals(array('w' => 'wwwww', 'x' => 'x', 'y' => 'y', 'z' => 'default-z','_format' => 'html', '_route' => 'test'), $matcher->match('/wwwwwxy'));

        $this->setExpectedException('Symfony_Component_Routing_Exception_ResourceNotFoundException');
        $matcher->match('/wxy.html');
    }

    public function testOptionalVariableWithNoRealSeparator()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('test', new Symfony_Component_Routing_Route('/get{what}', array('what' => 'All')));
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext());

        $this->assertEquals(array('what' => 'All', '_route' => 'test'), $matcher->match('/get'));
        $this->assertEquals(array('what' => 'Sites', '_route' => 'test'), $matcher->match('/getSites'));

        // Usually the character in front of an optional parameter can be left out, e.g. with pattern '/get/{what}' just '/get' would match.
        // But here the 't' in 'get' is not a separating character, so it makes no sense to match without it.
        $this->setExpectedException('Symfony_Component_Routing_Exception_ResourceNotFoundException');
        $matcher->match('/ge');
    }

    public function testRequiredVariableWithNoRealSeparator()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('test', new Symfony_Component_Routing_Route('/get{what}Suffix'));
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext());

        $this->assertEquals(array('what' => 'Sites', '_route' => 'test'), $matcher->match('/getSitesSuffix'));
    }

    public function testDefaultRequirementOfVariable()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('test', new Symfony_Component_Routing_Route('/{page}.{_format}'));
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext());

        $this->assertEquals(array('page' => 'index', '_format' => 'mobile.html', '_route' => 'test'), $matcher->match('/index.mobile.html'));
    }

    /**
     * @expectedException Symfony_Component_Routing_Exception_ResourceNotFoundException
     */
    public function testDefaultRequirementOfVariableDisallowsSlash()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('test', new Symfony_Component_Routing_Route('/{page}.{_format}'));
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext());

        $matcher->match('/index.sl/ash');
    }

    /**
     * @expectedException Symfony_Component_Routing_Exception_ResourceNotFoundException
     */
    public function testDefaultRequirementOfVariableDisallowsNextSeparator()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('test', new Symfony_Component_Routing_Route('/{page}.{_format}', array(), array('_format' => 'html|xml')));
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext());

        $matcher->match('/do.t.html');
    }

    /**
     * @expectedException Symfony_Component_Routing_Exception_ResourceNotFoundException
     */
    public function testSchemeRequirement()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo', new Symfony_Component_Routing_Route('/foo', array(), array('_scheme' => 'https')));
        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext());
        $matcher->match('/foo');
    }

    public function testDecodeOnce()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo', new Symfony_Component_Routing_Route('/foo/{foo}'));

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext());
        $this->assertEquals(array('foo' => 'bar%23', '_route' => 'foo'), $matcher->match('/foo/bar%2523'));
    }

    public function testCannotRelyOnPrefix()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();

        $subColl = new Symfony_Component_Routing_RouteCollection();
        $subColl->add('bar', new Symfony_Component_Routing_Route('/bar'));
        $subColl->addPrefix('/prefix');
        // overwrite the pattern, so the prefix is not valid anymore for this route in the collection
        $subColl->get('bar')->setPattern('/new');

        $coll->addCollection($subColl);

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext());
        $this->assertEquals(array('_route' => 'bar'), $matcher->match('/new'));
    }

    public function testWithHost()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo', new Symfony_Component_Routing_Route('/foo/{foo}', array(), array(), array(), '{locale}.example.com'));

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext('', 'GET', 'en.example.com'));
        $this->assertEquals(array('foo' => 'bar', '_route' => 'foo', 'locale' => 'en'), $matcher->match('/foo/bar'));
    }

    public function testWithHostOnRouteCollection()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo', new Symfony_Component_Routing_Route('/foo/{foo}'));
        $coll->add('bar', new Symfony_Component_Routing_Route('/bar/{foo}', array(), array(), array(), '{locale}.example.net'));
        $coll->setHost('{locale}.example.com');

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext('', 'GET', 'en.example.com'));
        $this->assertEquals(array('foo' => 'bar', '_route' => 'foo', 'locale' => 'en'), $matcher->match('/foo/bar'));

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext('', 'GET', 'en.example.com'));
        $this->assertEquals(array('foo' => 'bar', '_route' => 'bar', 'locale' => 'en'), $matcher->match('/bar/bar'));
    }

    /**
     * @expectedException Symfony_Component_Routing_Exception_ResourceNotFoundException
     */
    public function testWithOutHostHostDoesNotMatch()
    {
        $coll = new Symfony_Component_Routing_RouteCollection();
        $coll->add('foo', new Symfony_Component_Routing_Route('/foo/{foo}', array(), array(), array(), '{locale}.example.com'));

        $matcher = new Symfony_Component_Routing_Matcher_UrlMatcher($coll, new Symfony_Component_Routing_RequestContext('', 'GET', 'example.com'));
        $matcher->match('/foo/bar');
    }
}
