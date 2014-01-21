<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('UPLOAD_ERR_EXTENSION')) {
    define('UPLOAD_ERR_EXTENSION', 8);
}

class Symfony_Component_HttpFoundation_Tests_File_UploadedFileTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!ini_get('file_uploads')) {
            $this->markTestSkipped('file_uploads is disabled in php.ini');
        }
    }

    public function testConstructWhenFileNotExists()
    {
        $this->setExpectedException('Symfony_Component_HttpFoundation_File_Exception_FileNotFoundException');

        new Symfony_Component_HttpFoundation_File_UploadedFile(
            dirname(__FILE__).'/Fixtures/not_here',
            'original.gif',
            null
        );
    }

    public function testFileUploadsWithNoMimeType()
    {
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile(
            dirname(__FILE__).'/Fixtures/test.gif',
            'original.gif',
            null,
            filesize(dirname(__FILE__).'/Fixtures/test.gif'),
            UPLOAD_ERR_OK
        );

        $this->assertEquals('application/octet-stream', $file->getClientMimeType());

        if (extension_loaded('fileinfo')) {
            $this->assertEquals('image/gif', $file->getMimeType());
        }
    }

    public function testFileUploadsWithUnknownMimeType()
    {
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile(
            dirname(__FILE__).'/Fixtures/.unknownextension',
            'original.gif',
            null,
            filesize(dirname(__FILE__).'/Fixtures/.unknownextension'),
            UPLOAD_ERR_OK
        );

        $this->assertEquals('application/octet-stream', $file->getClientMimeType());
    }

    public function testErrorIsOkByDefault()
    {
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile(
            dirname(__FILE__).'/Fixtures/test.gif',
            'original.gif',
            'image/gif',
            filesize(dirname(__FILE__).'/Fixtures/test.gif'),
            null
        );

        $this->assertEquals(UPLOAD_ERR_OK, $file->getError());
    }

    public function testGetClientOriginalName()
    {
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile(
            dirname(__FILE__).'/Fixtures/test.gif',
            'original.gif',
            'image/gif',
            filesize(dirname(__FILE__).'/Fixtures/test.gif'),
            null
        );

        $this->assertEquals('original.gif', $file->getClientOriginalName());
    }

    public function testGetClientOriginalExtension()
    {
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile(
            dirname(__FILE__).'/Fixtures/test.gif',
            'original.gif',
            'image/gif',
            filesize(dirname(__FILE__).'/Fixtures/test.gif'),
            null
        );

        $this->assertEquals('gif', $file->getClientOriginalExtension());
    }

    /**
     * @expectedException Symfony_Component_HttpFoundation_File_Exception_FileException
     */
    public function testMoveLocalFileIsNotAllowed()
    {
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile(
            dirname(__FILE__).'/Fixtures/test.gif',
            'original.gif',
            'image/gif',
            filesize(dirname(__FILE__).'/Fixtures/test.gif'),
            UPLOAD_ERR_OK
        );

        $movedFile = $file->move(dirname(__FILE__).'/Fixtures/directory');
    }

    public function testMoveLocalFileIsAllowedInTestMode()
    {
        $path = dirname(__FILE__).'/Fixtures/test.copy.gif';
        $targetDir = dirname(__FILE__).'/Fixtures/directory';
        $targetPath = $targetDir.'/test.copy.gif';
        @unlink($path);
        @unlink($targetPath);
        copy(dirname(__FILE__).'/Fixtures/test.gif', $path);

        $file = new Symfony_Component_HttpFoundation_File_UploadedFile(
            $path,
            'original.gif',
            'image/gif',
            filesize($path),
            UPLOAD_ERR_OK,
            true
        );

        $movedFile = $file->move(dirname(__FILE__).'/Fixtures/directory');

        $this->assertTrue(file_exists($targetPath));
        $this->assertFalse(file_exists($path));
        $this->assertEquals(realpath($targetPath), $movedFile->getRealPath());

        @unlink($targetPath);
    }

    public function testGetClientOriginalNameSanitizeFilename()
    {
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile(
            dirname(__FILE__).'/Fixtures/test.gif',
            '../../original.gif',
            'image/gif',
            filesize(dirname(__FILE__).'/Fixtures/test.gif'),
            null
        );

        $this->assertEquals('original.gif', $file->getClientOriginalName());
    }

    public function testGetSize()
    {
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile(
            dirname(__FILE__).'/Fixtures/test.gif',
            'original.gif',
            'image/gif',
            filesize(dirname(__FILE__).'/Fixtures/test.gif'),
            null
        );

        $this->assertEquals(filesize(dirname(__FILE__).'/Fixtures/test.gif'), $file->getSize());

        $file = new Symfony_Component_HttpFoundation_File_UploadedFile(
            dirname(__FILE__).'/Fixtures/test',
            'original.gif',
            'image/gif'
        );

        $this->assertEquals(filesize(dirname(__FILE__).'/Fixtures/test'), $file->getSize());
    }

    public function testGetExtension()
    {
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile(
            dirname(__FILE__).'/Fixtures/test.gif',
            'original.gif',
            null
        );

        $this->assertEquals('gif', $file->getExtension());
    }

    public function testIsValid()
    {
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile(
            dirname(__FILE__).'/Fixtures/test.gif',
            'original.gif',
            null,
            filesize(dirname(__FILE__).'/Fixtures/test.gif'),
            UPLOAD_ERR_OK
        );

        $this->assertTrue($file->isValid());
    }

    /**
     * @dataProvider uploadedFileErrorProvider
     */
    public function testIsInvalidOnUploadError($error)
    {
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile(
            dirname(__FILE__).'/Fixtures/test.gif',
            'original.gif',
            null,
            filesize(dirname(__FILE__).'/Fixtures/test.gif'),
            $error
        );

        $this->assertFalse($file->isValid());
    }

    public function uploadedFileErrorProvider()
    {
        return array(
            array(UPLOAD_ERR_INI_SIZE),
            array(UPLOAD_ERR_FORM_SIZE),
            array(UPLOAD_ERR_PARTIAL),
            array(UPLOAD_ERR_NO_TMP_DIR),
            array(UPLOAD_ERR_EXTENSION),
        );
    }
}
