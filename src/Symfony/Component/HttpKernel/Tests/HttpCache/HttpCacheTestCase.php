<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_HttpCache_HttpCacheTestCase extends PHPUnit_Framework_TestCase
{
    protected $kernel;
    protected $cache;
    protected $caches;
    protected $cacheConfig;
    protected $request;
    protected $response;
    protected $responses;
    protected $catch;
    protected $esi;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        $this->kernel = null;

        $this->cache = null;
        $this->esi = null;
        $this->caches = array();
        $this->cacheConfig = array();

        $this->request = null;
        $this->response = null;
        $this->responses = array();

        $this->catch = false;

        $this->clearDirectory(sys_get_temp_dir().'/http_cache');
    }

    protected function tearDown()
    {
        $this->kernel = null;
        $this->cache = null;
        $this->caches = null;
        $this->request = null;
        $this->response = null;
        $this->responses = null;
        $this->cacheConfig = null;
        $this->catch = null;
        $this->esi = null;

        $this->clearDirectory(sys_get_temp_dir().'/http_cache');
    }

    public function assertHttpKernelIsCalled()
    {
        $this->assertTrue($this->kernel->hasBeenCalled());
    }

    public function assertHttpKernelIsNotCalled()
    {
        $this->assertFalse($this->kernel->hasBeenCalled());
    }

    public function assertResponseOk()
    {
        $this->assertEquals(200, $this->response->getStatusCode());
    }

    public function assertTraceContains($trace)
    {
        $traces = $this->cache->getTraces();
        $traces = current($traces);

        $this->assertRegExp('/'.$trace.'/', implode(', ', $traces));
    }

    public function assertTraceNotContains($trace)
    {
        $traces = $this->cache->getTraces();
        $traces = current($traces);

        $this->assertNotRegExp('/'.$trace.'/', implode(', ', $traces));
    }

    public function assertExceptionsAreCaught()
    {
        $this->assertTrue($this->kernel->isCatchingExceptions());
    }

    public function assertExceptionsAreNotCaught()
    {
        $this->assertFalse($this->kernel->isCatchingExceptions());
    }

    public function request($method, $uri = '/', $server = array(), $cookies = array(), $esi = false)
    {
        if (null === $this->kernel) {
            throw new LogicException('You must call setNextResponse() before calling request().');
        }

        $this->kernel->reset();

        $this->store = new Symfony_Component_HttpKernel_HttpCache_Store(sys_get_temp_dir().'/http_cache');

        $this->cacheConfig['debug'] = true;

        $this->esi = $esi ? new Symfony_Component_HttpKernel_HttpCache_Esi() : null;
        $this->cache = new Symfony_Component_HttpKernel_HttpCache_HttpCache($this->kernel, $this->store, $this->esi, $this->cacheConfig);
        $this->request = Symfony_Component_HttpFoundation_Request::create($uri, $method, array(), $cookies, array(), $server);

        $this->response = $this->cache->handle($this->request, Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST, $this->catch);

        $this->responses[] = $this->response;
    }

    public function getMetaStorageValues()
    {
        $values = array();
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(sys_get_temp_dir().'/http_cache/md')) as $file) {
            if ($file->isFile()) {
                $values[] = file_get_contents($file);
            }
        }

        return $values;
    }

    // A basic response with 200 status code and a tiny body.
    public function setNextResponse($statusCode = 200, array $headers = array(), $body = 'Hello World', $customizer = null)
    {
        $this->kernel = new Symfony_Component_HttpKernel_Tests_HttpCache_TestHttpKernel($body, $statusCode, $headers, $customizer);
    }

    public function setNextResponses($responses)
    {
        $this->kernel = new Symfony_Component_HttpKernel_Tests_HttpCache_TestMultipleHttpKernel($responses);
    }

    public function catchExceptions($catch = true)
    {
        $this->catch = $catch;
    }

    public static function clearDirectory($directory)
    {
        if (!is_dir($directory)) {
            return;
        }

        $fp = opendir($directory);
        while (false !== $file = readdir($fp)) {
            if (!in_array($file, array('.', '..'))) {
                if (is_link($directory.'/'.$file)) {
                    unlink($directory.'/'.$file);
                } elseif (is_dir($directory.'/'.$file)) {
                    self::clearDirectory($directory.'/'.$file);
                    rmdir($directory.'/'.$file);
                } else {
                    unlink($directory.'/'.$file);
                }
            }
        }

        closedir($fp);
    }
}
