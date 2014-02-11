<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * FileBagTest.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class Symfony_Component_HttpFoundation_Tests_FileBagTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testFileMustBeAnArrayOrUploadedFile()
    {
        new Symfony_Component_HttpFoundation_FileBag(array('file' => 'foo'));
    }

    public function testShouldConvertsUploadedFiles()
    {
        $tmpFile = $this->createTempFile();
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile($tmpFile, basename($tmpFile), 'text/plain', 100, 0);

        $bag = new Symfony_Component_HttpFoundation_FileBag(array('file' => array(
            'name' => basename($tmpFile),
            'type' => 'text/plain',
            'tmp_name' => $tmpFile,
            'error' => 0,
            'size' => 100
        )));

        $this->assertEquals($file, $bag->get('file'));
    }

    public function testShouldSetEmptyUploadedFilesToNull()
    {
        $bag = new Symfony_Component_HttpFoundation_FileBag(array('file' => array(
            'name' => '',
            'type' => '',
            'tmp_name' => '',
            'error' => UPLOAD_ERR_NO_FILE,
            'size' => 0
        )));

        $this->assertNull($bag->get('file'));
    }

    public function testShouldConvertUploadedFilesWithPhpBug()
    {
        $tmpFile = $this->createTempFile();
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile($tmpFile, basename($tmpFile), 'text/plain', 100, 0);

        $bag = new Symfony_Component_HttpFoundation_FileBag(array(
            'child' => array(
                'name' => array(
                    'file' => basename($tmpFile),
                ),
                'type' => array(
                    'file' => 'text/plain',
                ),
                'tmp_name' => array(
                    'file' => $tmpFile,
                ),
                'error' => array(
                    'file' => 0,
                ),
                'size' => array(
                    'file' => 100,
                ),
            )
        ));

        $files = $bag->all();
        $this->assertEquals($file, $files['child']['file']);
    }

    public function testShouldConvertNestedUploadedFilesWithPhpBug()
    {
        $tmpFile = $this->createTempFile();
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile($tmpFile, basename($tmpFile), 'text/plain', 100, 0);

        $bag = new Symfony_Component_HttpFoundation_FileBag(array(
            'child' => array(
                'name' => array(
                    'sub' => array('file' => basename($tmpFile))
                ),
                'type' => array(
                    'sub' => array('file' => 'text/plain')
                ),
                'tmp_name' => array(
                    'sub' => array('file' => $tmpFile)
                ),
                'error' => array(
                    'sub' => array('file' => 0)
                ),
                'size' => array(
                    'sub' => array('file' => 100)
                ),
            )
        ));

        $files = $bag->all();
        $this->assertEquals($file, $files['child']['sub']['file']);
    }

    public function testShouldNotConvertNestedUploadedFiles()
    {
        $tmpFile = $this->createTempFile();
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile($tmpFile, basename($tmpFile), 'text/plain', 100, 0);
        $bag = new Symfony_Component_HttpFoundation_FileBag(array('image' => array('file' => $file)));

        $files = $bag->all();
        $this->assertEquals($file, $files['image']['file']);
    }

    protected function createTempFile()
    {
        return tempnam(sys_get_temp_dir().'/form_test', 'FormTest');
    }

    protected function setUp()
    {
        mkdir(sys_get_temp_dir().'/form_test', 0777, true);
    }

    protected function tearDown()
    {
        foreach (glob(sys_get_temp_dir().'/form_test/*') as $file) {
            unlink($file);
        }

        rmdir(sys_get_temp_dir().'/form_test');
    }
}
