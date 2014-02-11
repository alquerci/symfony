<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpFoundation_Tests_BinaryFileResponseTest extends PHPUnit_Framework_TestCase
{
    public function testConstruction()
    {
        $response = new Symfony_Component_HttpFoundation_BinaryFileResponse(dirname(__FILE__).'/../README.md', 404, array('X-Header' => 'Foo'), true, null, true, true);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Foo', $response->headers->get('X-Header'));
        $this->assertTrue($response->headers->has('ETag'));
        $this->assertTrue($response->headers->has('Last-Modified'));
        $this->assertFalse($response->headers->has('Content-Disposition'));

        $response = Symfony_Component_HttpFoundation_BinaryFileResponse::create(dirname(__FILE__).'/../README.md', 404, array(), true, Symfony_Component_HttpFoundation_ResponseHeaderBag::DISPOSITION_INLINE);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertFalse($response->headers->has('ETag'));
        $this->assertEquals('inline; filename="README.md"', $response->headers->get('Content-Disposition'));
    }

    /**
     * @expectedException LogicException
     */
    public function testSetContent()
    {
        $response = new Symfony_Component_HttpFoundation_BinaryFileResponse(dirname(__FILE__).'/../README.md');
        $response->setContent('foo');
    }

    public function testGetContent()
    {
        $response = new Symfony_Component_HttpFoundation_BinaryFileResponse(dirname(__FILE__).'/../README.md');
        $this->assertFalse($response->getContent());
    }

    /**
     * @dataProvider provideRanges
     */
    public function testRequests($requestRange, $offset, $length, $responseRange)
    {
        $response = Symfony_Component_HttpFoundation_BinaryFileResponse::create(dirname(__FILE__).'/File/Fixtures/test.gif')->setAutoEtag();

        // do a request to get the ETag
        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $response->prepare($request);
        $etag = $response->headers->get('ETag');

        // prepare a request for a range of the testing file
        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $request->headers->set('If-Range', $etag);
        $request->headers->set('Range', $requestRange);

        $file = fopen(dirname(__FILE__).'/File/Fixtures/test.gif', 'r');
        fseek($file, $offset);
        $data = fread($file, $length);
        fclose($file);

        $this->expectOutputString($data);
        $response = clone $response;
        $response->prepare($request);
        $response->sendContent();

        $this->assertEquals(206, $response->getStatusCode());
        $this->assertEquals('binary', $response->headers->get('Content-Transfer-Encoding'));
        $this->assertEquals($responseRange, $response->headers->get('Content-Range'));
    }

    public function provideRanges()
    {
        return array(
            array('bytes=1-4', 1, 4, 'bytes 1-4/35'),
            array('bytes=-5', 30, 5, 'bytes 30-34/35'),
            array('bytes=-35', 0, 35, 'bytes 0-34/35'),
            array('bytes=-40', 0, 35, 'bytes 0-34/35'),
            array('bytes=30-', 30, 5, 'bytes 30-34/35'),
            array('bytes=30-30', 30, 1, 'bytes 30-30/35'),
            array('bytes=30-34', 30, 5, 'bytes 30-34/35'),
            array('bytes=30-40', 30, 5, 'bytes 30-34/35')
        );
    }

    public function testXSendfile()
    {
        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $request->headers->set('X-Sendfile-Type', 'X-Sendfile');

        Symfony_Component_HttpFoundation_BinaryFileResponse::trustXSendfileTypeHeader();
        $response = Symfony_Component_HttpFoundation_BinaryFileResponse::create(dirname(__FILE__).'/../README.md');
        $response->prepare($request);

        $this->expectOutputString('');
        $response->sendContent();

        $this->assertContains('README.md', $response->headers->get('X-Sendfile'));
    }

    /**
     * @dataProvider getSampleXAccelMappings
     */
    public function testXAccelMapping($realpath, $mapping, $virtual)
    {
        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $request->headers->set('X-Sendfile-Type', 'X-Accel-Redirect');
        $request->headers->set('X-Accel-Mapping', $mapping);

        $file = $this->getMockBuilder('Symfony_Component_HttpFoundation_File_File')
                     ->disableOriginalConstructor()
                     ->getMock();
        $file->expects($this->any())
             ->method('getRealPath')
             ->will($this->returnValue($realpath));
        $file->expects($this->any())
             ->method('isReadable')
             ->will($this->returnValue(true));

        Symfony_Component_HttpFoundation_BinaryFileResponse::trustXSendFileTypeHeader();
        $response = new Symfony_Component_HttpFoundation_Tests_BinaryFileResponse(dirname(__FILE__).'/../README.md');
        $response->setFileProperty($file);

        $response->prepare($request);
        $this->assertEquals($virtual, $response->headers->get('X-Accel-Redirect'));
    }

    public function getSampleXAccelMappings()
    {
        return array(
            array('/var/www/var/www/files/foo.txt', '/files/=/var/www/', '/files/var/www/files/foo.txt'),
            array('/home/foo/bar.txt', '/files/=/var/www/,/baz/=/home/foo/', '/baz/bar.txt'),
        );
    }
}

class Symfony_Component_HttpFoundation_Tests_BinaryFileResponse extends Symfony_Component_HttpFoundation_BinaryFileResponse
{
    public function setFileProperty(Symfony_Component_HttpFoundation_File_File $file)
    {
        $this->file = $file;
    }
}
