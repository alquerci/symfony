<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_ClientTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_BrowserKit_Client')) {
            $this->markTestSkipped('The "BrowserKit" component is not available');
        }
    }

    public function testDoRequest()
    {
        $client = new Symfony_Component_HttpKernel_Client(new Symfony_Component_HttpKernel_Tests_TestHttpKernel());

        $client->request('GET', '/');
        $this->assertEquals('Request: /', $client->getResponse()->getContent(), '->doRequest() uses the request handler to make the request');

        $client->request('GET', 'http://www.example.com/');
        $this->assertEquals('Request: /', $client->getResponse()->getContent(), '->doRequest() uses the request handler to make the request');
        $this->assertEquals('www.example.com', $client->getRequest()->getHost(), '->doRequest() uses the request handler to make the request');

        $client->request('GET', 'http://www.example.com/?parameter=http://google.com');
        $this->assertEquals('http://www.example.com/?parameter='.urlencode('http://google.com'), $client->getRequest()->getUri(), '->doRequest() uses the request handler to make the request');
    }

    public function testGetScript()
    {
        if (!class_exists('Symfony_Component_Process_Process')) {
            $this->markTestSkipped('The "Process" component is not available');
        }

        if (!class_exists('Symfony_Component_ClassLoader_ClassLoader')) {
            $this->markTestSkipped('The "ClassLoader" component is not available');
        }

        $client = new Symfony_Component_HttpKernel_Tests_Fixtures_TestClient(new Symfony_Component_HttpKernel_Tests_TestHttpKernel());
        $client->insulate();
        $client->request('GET', '/');

        $this->assertEquals('Request: /', $client->getResponse()->getContent(), '->getScript() returns a script that uses the request handler to make the request');
    }

    public function testFilterResponseConvertsCookies()
    {
        $client = new Symfony_Component_HttpKernel_Tests_Fixtures_TestClient(new Symfony_Component_HttpKernel_Tests_TestHttpKernel());

        $r = new ReflectionObject($client);
        $m = $r->getMethod('filterResponse');

        $expected = array(
            'foo=bar; expires=Sun, 15 Feb 2009 20:00:00 GMT; domain=http://example.com; path=/foo; secure; httponly',
            'foo1=bar1; expires=Sun, 15 Feb 2009 20:00:00 GMT; domain=http://example.com; path=/foo; secure; httponly'
        );

        $response = new Symfony_Component_HttpFoundation_Response();
        $response->headers->setCookie(new Symfony_Component_HttpFoundation_Cookie('foo', 'bar', DateTime::createFromFormat('j-M-Y H:i:s T', '15-Feb-2009 20:00:00 GMT')->format('U'), '/foo', 'http://example.com', true, true));
        $domResponse = $m->invoke($client, $response);
        $this->assertEquals($expected[0], $domResponse->getHeader('Set-Cookie')->__toString());

        $response = new Symfony_Component_HttpFoundation_Response();
        $response->headers->setCookie(new Symfony_Component_HttpFoundation_Cookie('foo', 'bar', DateTime::createFromFormat('j-M-Y H:i:s T', '15-Feb-2009 20:00:00 GMT')->format('U'), '/foo', 'http://example.com', true, true));
        $response->headers->setCookie(new Symfony_Component_HttpFoundation_Cookie('foo1', 'bar1', DateTime::createFromFormat('j-M-Y H:i:s T', '15-Feb-2009 20:00:00 GMT')->format('U'), '/foo', 'http://example.com', true, true));
        $domResponse = $m->invoke($client, $response);
        $this->assertEquals($expected[0], $domResponse->getHeader('Set-Cookie')->__toString());
        $cookies = $domResponse->getHeader('Set-Cookie', false);
        $this->assertEquals($expected[0], $cookies[0]->__toString());
        $this->assertEquals($expected[1], $cookies[1]->__toString());
    }

    public function testFilterResponseSupportsStreamedResponses()
    {
        $client = new Symfony_Component_HttpKernel_Tests_Fixtures_TestClient(new Symfony_Component_HttpKernel_Tests_TestHttpKernel());

        $r = new ReflectionObject($client);
        $m = $r->getMethod('filterResponse');

        $response = new Symfony_Component_HttpFoundation_StreamedResponse(create_function('', "
            echo 'foo';
        "));

        $domResponse = $m->invoke($client, $response);
        $this->assertEquals('foo', $domResponse->getContent());
    }

    public function testUploadedFile()
    {
        $source = tempnam(sys_get_temp_dir(), 'source');
        $target = sys_get_temp_dir().'/sf.moved.file';
        @unlink($target);

        $kernel = new Symfony_Component_HttpKernel_Tests_TestHttpKernel();
        $client = new Symfony_Component_HttpKernel_Client($kernel);

        $files = array(
            array('tmp_name' => $source, 'name' => 'original', 'type' => 'mime/original', 'size' => 123, 'error' => UPLOAD_ERR_OK),
            new Symfony_Component_HttpFoundation_File_UploadedFile($source, 'original', 'mime/original', 123, UPLOAD_ERR_OK),
        );

        foreach ($files as $file) {
            $client->request('POST', '/', array(), array('foo' => $file));

            $files = $client->getRequest()->files->all();

            $this->assertEquals(1, count($files));

            $file = $files['foo'];

            $this->assertEquals('original', $file->getClientOriginalName());
            $this->assertEquals('mime/original', $file->getClientMimeType());
            $this->assertEquals('123', $file->getClientSize());
            $this->assertTrue($file->isValid());
        }

        $file->move(dirname($target), basename($target));

        $this->assertFileExists($target);
        unlink($target);
    }

    public function testUploadedFileWhenSizeExceedsUploadMaxFileSize()
    {
        $source = tempnam(sys_get_temp_dir(), 'source');

        $kernel = new Symfony_Component_HttpKernel_Tests_TestHttpKernel();
        $client = new Symfony_Component_HttpKernel_Client($kernel);

        $file = $this->getMock(
            'Symfony_Component_HttpFoundation_File_UploadedFile',
            array('getSize'),
            array($source, 'original', 'mime/original', 123, UPLOAD_ERR_OK)
        );

        $file->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(INF))
        ;

        $client->request('POST', '/', array(), array($file));

        $files = $client->getRequest()->files->all();

        $this->assertEquals(1, count($files));

        $file = $files[0];

        $this->assertFalse($file->isValid());
        $this->assertEquals(UPLOAD_ERR_INI_SIZE, $file->getError());
        $this->assertEquals('mime/original', $file->getClientMimeType());
        $this->assertEquals('original', $file->getClientOriginalName());
        $this->assertEquals(0, $file->getClientSize());

        unlink($source);
    }
}
