<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpFoundation_Tests_File_FileTest extends PHPUnit_Framework_TestCase
{
    protected $file;

    public function testGetMimeTypeUsesMimeTypeGuessers()
    {
        $file = new Symfony_Component_HttpFoundation_File_File(dirname(__FILE__).'/Fixtures/test.gif');
        $guesser = $this->createMockGuesser($file->getPathname(), 'image/gif');

        Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser::getInstance()->register($guesser);

        $this->assertEquals('image/gif', $file->getMimeType());
    }

    public function testGuessExtensionWithoutGuesser()
    {
        $file = new Symfony_Component_HttpFoundation_File_File(dirname(__FILE__).'/Fixtures/directory/.empty');

        $this->assertNull($file->guessExtension());
    }

    public function testGuessExtensionIsBasedOnMimeType()
    {
        $file = new Symfony_Component_HttpFoundation_File_File(dirname(__FILE__).'/Fixtures/test');
        $guesser = $this->createMockGuesser($file->getPathname(), 'image/gif');

        Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser::getInstance()->register($guesser);

        $this->assertEquals('gif', $file->guessExtension());
    }

    public function testConstructWhenFileNotExists()
    {
        $this->setExpectedException('Symfony_Component_HttpFoundation_File_Exception_FileNotFoundException');

        new Symfony_Component_HttpFoundation_File_File(dirname(__FILE__).'/Fixtures/not_here');
    }

    public function testMove()
    {
        $path = dirname(__FILE__).'/Fixtures/test.copy.gif';
        $targetDir = dirname(__FILE__).'/Fixtures/directory';
        $targetPath = $targetDir.'/test.copy.gif';
        @unlink($path);
        @unlink($targetPath);
        copy(dirname(__FILE__).'/Fixtures/test.gif', $path);

        $file = new Symfony_Component_HttpFoundation_File_File($path);
        $movedFile = $file->move($targetDir);
        $this->assertThat($movedFile, $this->isInstanceOf('Symfony_Component_HttpFoundation_File_File'));

        $this->assertTrue(file_exists($targetPath));
        $this->assertFalse(file_exists($path));
        $this->assertEquals(realpath($targetPath), $movedFile->getRealPath());

        @unlink($targetPath);
    }

    public function testMoveWithNewName()
    {
        $path = dirname(__FILE__).'/Fixtures/test.copy.gif';
        $targetDir = dirname(__FILE__).'/Fixtures/directory';
        $targetPath = $targetDir.'/test.newname.gif';
        @unlink($path);
        @unlink($targetPath);
        copy(dirname(__FILE__).'/Fixtures/test.gif', $path);

        $file = new Symfony_Component_HttpFoundation_File_File($path);
        $movedFile = $file->move($targetDir, 'test.newname.gif');

        $this->assertTrue(file_exists($targetPath));
        $this->assertFalse(file_exists($path));
        $this->assertEquals(realpath($targetPath), $movedFile->getRealPath());

        @unlink($targetPath);
    }

    public function getFilenameFixtures()
    {
        return array(
            array('original.gif', 'original.gif'),
            array('..\\..\\original.gif', 'original.gif'),
            array('../../original.gif', 'original.gif'),
            array('файлfile.gif', 'файлfile.gif'),
            array('..\\..\\файлfile.gif', 'файлfile.gif'),
            array('../../файлfile.gif', 'файлfile.gif'),
        );
    }

    /**
     * @dataProvider getFilenameFixtures
     */
    public function testMoveWithNonLatinName($filename, $sanitizedFilename)
    {
        $path = dirname(__FILE__).'/Fixtures/'.$sanitizedFilename;
        $targetDir = dirname(__FILE__).'/Fixtures/directory/';
        $targetPath = $targetDir.$sanitizedFilename;
        @unlink($path);
        @unlink($targetPath);
        copy(dirname(__FILE__).'/Fixtures/test.gif', $path);

        $file = new Symfony_Component_HttpFoundation_File_File($path);
        $movedFile = $file->move($targetDir,$filename);
        $this->assertThat($movedFile, $this->isInstanceOf('Symfony_Component_HttpFoundation_File_File'));

        $this->assertTrue(file_exists($targetPath));
        $this->assertFalse(file_exists($path));
        $this->assertEquals(realpath($targetPath), $movedFile->getRealPath());

        @unlink($targetPath);
    }

    public function testMoveToAnUnexistentDirectory()
    {
        $sourcePath = dirname(__FILE__).'/Fixtures/test.copy.gif';
        $targetDir = dirname(__FILE__).'/Fixtures/directory/sub';
        $targetPath = $targetDir.'/test.copy.gif';
        @unlink($sourcePath);
        @unlink($targetPath);
        @rmdir($targetDir);
        copy(dirname(__FILE__).'/Fixtures/test.gif', $sourcePath);

        $file = new Symfony_Component_HttpFoundation_File_File($sourcePath);
        $movedFile = $file->move($targetDir);

        $this->assertFileExists($targetPath);
        $this->assertFileNotExists($sourcePath);
        $this->assertEquals(realpath($targetPath), $movedFile->getRealPath());

        @unlink($sourcePath);
        @unlink($targetPath);
        @rmdir($targetDir);
    }

    public function testGetExtension()
    {
        $file = new Symfony_Component_HttpFoundation_File_File(dirname(__FILE__).'/Fixtures/test.gif');
        $this->assertEquals('gif', $file->getExtension());
    }

    protected function createMockGuesser($path, $mimeType)
    {
        $guesser = $this->getMock('Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesserInterface');
        $guesser
            ->expects($this->once())
            ->method('guess')
            ->with($this->equalTo($path))
            ->will($this->returnValue($mimeType))
        ;

        return $guesser;
    }
}
