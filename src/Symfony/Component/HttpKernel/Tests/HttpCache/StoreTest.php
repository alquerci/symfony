<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_HttpCache_StoreTest extends PHPUnit_Framework_TestCase
{
    protected $request;
    protected $response;
    protected $store;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        $this->request = Symfony_Component_HttpFoundation_Request::create('/');
        $this->response = new Symfony_Component_HttpFoundation_Response('hello world', 200, array());

        Symfony_Component_HttpKernel_Tests_HttpCache_HttpCacheTestCase::clearDirectory(sys_get_temp_dir().'/http_cache');

        $this->store = new Symfony_Component_HttpKernel_HttpCache_Store(sys_get_temp_dir().'/http_cache');
    }

    protected function tearDown()
    {
        $this->store = null;
        $this->request = null;
        $this->response = null;

        Symfony_Component_HttpKernel_Tests_HttpCache_HttpCacheTestCase::clearDirectory(sys_get_temp_dir().'/http_cache');
    }

    public function testReadsAnEmptyArrayWithReadWhenNothingCachedAtKey()
    {
        $this->assertEmpty($this->getStoreMetadata('/nothing'));
    }

    public function testUnlockFileThatDoesExist()
    {
        $cacheKey = $this->storeSimpleEntry();
        $this->store->lock($this->request);

        $this->assertTrue($this->store->unlock($this->request));
    }

    public function testUnlockFileThatDoesNotExist()
    {
        $this->assertFalse($this->store->unlock($this->request));
    }

    public function testRemovesEntriesForKeyWithPurge()
    {
        $request = Symfony_Component_HttpFoundation_Request::create('/foo');
        $this->store->write($request, new Symfony_Component_HttpFoundation_Response('foo'));

        $metadata = $this->getStoreMetadata($request);
        $this->assertNotEmpty($metadata);

        $this->assertTrue($this->store->purge('/foo'));
        $this->assertEmpty($this->getStoreMetadata($request));

        // cached content should be kept after purging
        $path = $this->store->getPath($metadata[0][1]['x-content-digest'][0]);
        $this->assertTrue(is_file($path));

        $this->assertFalse($this->store->purge('/bar'));
    }

    public function testStoresACacheEntry()
    {
        $cacheKey = $this->storeSimpleEntry();

        $this->assertNotEmpty($this->getStoreMetadata($cacheKey));
    }

    public function testSetsTheXContentDigestResponseHeaderBeforeStoring()
    {
        $cacheKey = $this->storeSimpleEntry();
        $entries = $this->getStoreMetadata($cacheKey);
        list ($req, $res) = $entries[0];

        $this->assertEquals('ena94a8fe5ccb19ba61c4c0873d391e987982fbbd3', $res['x-content-digest'][0]);
    }

    public function testFindsAStoredEntryWithLookup()
    {
        $this->storeSimpleEntry();
        $response = $this->store->lookup($this->request);

        $this->assertNotNull($response);
        $this->assertInstanceOf('Symfony_Component_HttpFoundation_Response', $response);
    }

    public function testDoesNotFindAnEntryWithLookupWhenNoneExists()
    {
        $request = Symfony_Component_HttpFoundation_Request::create('/test', 'get', array(), array(), array(), array('HTTP_FOO' => 'Foo', 'HTTP_BAR' => 'Bar'));

        $this->assertNull($this->store->lookup($request));
    }

    public function testCanonizesUrlsForCacheKeys()
    {
        $this->storeSimpleEntry($path = '/test?x=y&p=q');
        $hitsReq = Symfony_Component_HttpFoundation_Request::create($path);
        $missReq = Symfony_Component_HttpFoundation_Request::create('/test?p=x');

        $this->assertNotNull($this->store->lookup($hitsReq));
        $this->assertNull($this->store->lookup($missReq));
    }

    public function testDoesNotFindAnEntryWithLookupWhenTheBodyDoesNotExist()
    {
        $this->storeSimpleEntry();
        $this->assertNotNull($this->response->headers->get('X-Content-Digest'));
        $path = $this->getStorePath($this->response->headers->get('X-Content-Digest'));
        @unlink($path);
        $this->assertNull($this->store->lookup($this->request));
    }

    public function testRestoresResponseHeadersProperlyWithLookup()
    {
        $this->storeSimpleEntry();
        $response = $this->store->lookup($this->request);

        $this->assertEquals($response->headers->all(), array_merge(array('content-length' => 4, 'x-body-file' => array($this->getStorePath($response->headers->get('X-Content-Digest')))), $this->response->headers->all()));
    }

    public function testRestoresResponseContentFromEntityStoreWithLookup()
    {
        $this->storeSimpleEntry();
        $response = $this->store->lookup($this->request);
        $this->assertEquals($this->getStorePath('en'.sha1('test')), $response->getContent());
    }

    public function testInvalidatesMetaAndEntityStoreEntriesWithInvalidate()
    {
        $this->storeSimpleEntry();
        $this->store->invalidate($this->request);
        $response = $this->store->lookup($this->request);
        $this->assertInstanceOf('Symfony_Component_HttpFoundation_Response', $response);
        $this->assertFalse($response->isFresh());
    }

    public function testSucceedsQuietlyWhenInvalidateCalledWithNoMatchingEntries()
    {
        $req = Symfony_Component_HttpFoundation_Request::create('/test');
        $this->store->invalidate($req);
        $this->assertNull($this->store->lookup($this->request));
    }

    public function testDoesNotReturnEntriesThatVaryWithLookup()
    {
        $req1 = Symfony_Component_HttpFoundation_Request::create('/test', 'get', array(), array(), array(), array('HTTP_FOO' => 'Foo', 'HTTP_BAR' => 'Bar'));
        $req2 = Symfony_Component_HttpFoundation_Request::create('/test', 'get', array(), array(), array(), array('HTTP_FOO' => 'Bling', 'HTTP_BAR' => 'Bam'));
        $res = new Symfony_Component_HttpFoundation_Response('test', 200, array('Vary' => 'Foo Bar'));
        $this->store->write($req1, $res);

        $this->assertNull($this->store->lookup($req2));
    }

    public function testStoresMultipleResponsesForEachVaryCombination()
    {
        $req1 = Symfony_Component_HttpFoundation_Request::create('/test', 'get', array(), array(), array(), array('HTTP_FOO' => 'Foo', 'HTTP_BAR' => 'Bar'));
        $res1 = new Symfony_Component_HttpFoundation_Response('test 1', 200, array('Vary' => 'Foo Bar'));
        $key = $this->store->write($req1, $res1);

        $req2 = Symfony_Component_HttpFoundation_Request::create('/test', 'get', array(), array(), array(), array('HTTP_FOO' => 'Bling', 'HTTP_BAR' => 'Bam'));
        $res2 = new Symfony_Component_HttpFoundation_Response('test 2', 200, array('Vary' => 'Foo Bar'));
        $this->store->write($req2, $res2);

        $req3 = Symfony_Component_HttpFoundation_Request::create('/test', 'get', array(), array(), array(), array('HTTP_FOO' => 'Baz', 'HTTP_BAR' => 'Boom'));
        $res3 = new Symfony_Component_HttpFoundation_Response('test 3', 200, array('Vary' => 'Foo Bar'));
        $this->store->write($req3, $res3);

        $this->assertEquals($this->getStorePath('en'.sha1('test 3')), $this->store->lookup($req3)->getContent());
        $this->assertEquals($this->getStorePath('en'.sha1('test 2')), $this->store->lookup($req2)->getContent());
        $this->assertEquals($this->getStorePath('en'.sha1('test 1')), $this->store->lookup($req1)->getContent());

        $this->assertCount(3, $this->getStoreMetadata($key));
    }

    public function testOverwritesNonVaryingResponseWithStore()
    {
        $req1 = Symfony_Component_HttpFoundation_Request::create('/test', 'get', array(), array(), array(), array('HTTP_FOO' => 'Foo', 'HTTP_BAR' => 'Bar'));
        $res1 = new Symfony_Component_HttpFoundation_Response('test 1', 200, array('Vary' => 'Foo Bar'));
        $key = $this->store->write($req1, $res1);
        $this->assertEquals($this->getStorePath('en'.sha1('test 1')), $this->store->lookup($req1)->getContent());

        $req2 = Symfony_Component_HttpFoundation_Request::create('/test', 'get', array(), array(), array(), array('HTTP_FOO' => 'Bling', 'HTTP_BAR' => 'Bam'));
        $res2 = new Symfony_Component_HttpFoundation_Response('test 2', 200, array('Vary' => 'Foo Bar'));
        $this->store->write($req2, $res2);
        $this->assertEquals($this->getStorePath('en'.sha1('test 2')), $this->store->lookup($req2)->getContent());

        $req3 = Symfony_Component_HttpFoundation_Request::create('/test', 'get', array(), array(), array(), array('HTTP_FOO' => 'Foo', 'HTTP_BAR' => 'Bar'));
        $res3 = new Symfony_Component_HttpFoundation_Response('test 3', 200, array('Vary' => 'Foo Bar'));
        $key = $this->store->write($req3, $res3);
        $this->assertEquals($this->getStorePath('en'.sha1('test 3')), $this->store->lookup($req3)->getContent());

        $this->assertCount(2, $this->getStoreMetadata($key));
    }

    public function testLocking()
    {
        $req = Symfony_Component_HttpFoundation_Request::create('/test', 'get', array(), array(), array(), array('HTTP_FOO' => 'Foo', 'HTTP_BAR' => 'Bar'));
        $this->assertTrue($this->store->lock($req));

        $path = $this->store->lock($req);
        $this->assertTrue($this->store->isLocked($req));

        $this->store->unlock($req);
        $this->assertFalse($this->store->isLocked($req));
    }

    protected function storeSimpleEntry($path = null, $headers = array())
    {
        if (null === $path) {
            $path = '/test';
        }

        $this->request = Symfony_Component_HttpFoundation_Request::create($path, 'get', array(), array(), array(), $headers);
        $this->response = new Symfony_Component_HttpFoundation_Response('test', 200, array('Cache-Control' => 'max-age=420'));

        return $this->store->write($this->request, $this->response);
    }

    protected function getStoreMetadata($key)
    {
        if (version_compare(PHP_VERSION, '5.3.2', '<')) {
            $this->markTestSkipped('Require ReflectionMethod::setAccessible');
        }

        $r = new ReflectionObject($this->store);
        $m = $r->getMethod('getMetadata');
        $m->setAccessible(true);

        if ($key instanceof Symfony_Component_HttpFoundation_Request) {
            $m1 = $r->getMethod('getCacheKey');
            $m1->setAccessible(true);
            $key = $m1->invoke($this->store, $key);
        }

        return $m->invoke($this->store, $key);
    }

    protected function getStorePath($key)
    {
        return $this->store->getPath($key);
    }
}
