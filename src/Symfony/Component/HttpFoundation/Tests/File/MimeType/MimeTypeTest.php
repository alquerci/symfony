<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpFoundation_Tests_File_MimeType_MimeTypeTest extends PHPUnit_Framework_TestCase
{
    protected $path;

    public function testGuessImageWithoutExtension()
    {
        if (function_exists('finfo_open')) {
            $this->assertEquals('image/gif', Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser::getInstance()->guess(dirname(__FILE__).'/../Fixtures/test'));
        } else {
            $this->assertNull(Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser::getInstance()->guess(dirname(__FILE__).'/../Fixtures/test'));
        }
    }

    public function testGuessImageWithDirectory()
    {
        $this->setExpectedException('Symfony_Component_HttpFoundation_File_Exception_FileNotFoundException');

        Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser::getInstance()->guess(dirname(__FILE__).'/../Fixtures/directory');
    }

    public function testGuessImageWithFileBinaryMimeTypeGuesser()
    {
        $guesser = Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser::getInstance();
        $guesser->register(new Symfony_Component_HttpFoundation_File_MimeType_FileBinaryMimeTypeGuesser());
        if (function_exists('finfo_open')) {
            $this->assertEquals('image/gif', Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser::getInstance()->guess(dirname(__FILE__).'/../Fixtures/test'));
        } else {
            $this->assertNull(Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser::getInstance()->guess(dirname(__FILE__).'/../Fixtures/test'));
        }
    }

    public function testGuessImageWithKnownExtension()
    {
        if (function_exists('finfo_open')) {
            $this->assertEquals('image/gif', Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser::getInstance()->guess(dirname(__FILE__).'/../Fixtures/test.gif'));
        } else {
            $this->assertNull(Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser::getInstance()->guess(dirname(__FILE__).'/../Fixtures/test.gif'));
        }
    }

    public function testGuessFileWithUnknownExtension()
    {
        if (function_exists('finfo_open')) {
            $this->assertEquals('application/octet-stream', Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser::getInstance()->guess(dirname(__FILE__).'/../Fixtures/.unknownextension'));
        } else {
            $this->assertNull(Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser::getInstance()->guess(dirname(__FILE__).'/../Fixtures/.unknownextension'));
        }
    }

    public function testGuessWithIncorrectPath()
    {
        $this->setExpectedException('Symfony_Component_HttpFoundation_File_Exception_FileNotFoundException');
        Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser::getInstance()->guess(dirname(__FILE__).'/../Fixtures/not_here');
    }

    public function testGuessWithNonReadablePath()
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->markTestSkipped('Can not verify chmod operations on Windows');
        }

        if (in_array(get_current_user(), array('root'))) {
            $this->markTestSkipped('This test will fail if run under superuser');
        }

        $path = dirname(__FILE__).'/../Fixtures/to_delete';
        touch($path);
        @chmod($path, 0333);

        if (get_current_user() != 'root' && substr(sprintf('%o', fileperms($path)), -4) == '0333') {
            $this->setExpectedException('Symfony_Component_HttpFoundation_File_Exception_AccessDeniedException');
            Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser::getInstance()->guess($path);
        } else {
            $this->markTestSkipped('Can not verify chmod operations, change of file permissions failed');
        }
    }

    public static function tearDownAfterClass()
    {
        $path = dirname(__FILE__).'/../Fixtures/to_delete';
        if (file_exists($path)) {
            @chmod($path, 0666);
            @unlink($path);
        }
    }
}
