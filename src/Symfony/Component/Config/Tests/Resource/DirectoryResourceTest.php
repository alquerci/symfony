<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Config_Tests_Resource_DirectoryResourceTest extends PHPUnit_Framework_TestCase
{
    protected $directory;

    protected function setUp()
    {
        $this->directory = sys_get_temp_dir().'/symfonyDirectoryIterator';
        if (!file_exists($this->directory)) {
            mkdir($this->directory);
        }
        touch($this->directory.'/tmp.xml');
    }

    protected function tearDown()
    {
        if (!is_dir($this->directory)) {
            return;
        }
        $this->removeDirectory($this->directory);
    }

    protected function removeDirectory($directory)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $path) {
            if (preg_match('#[/\\\\]\.\.?$#', $path->__toString())) {
                continue;
            }
            if ($path->isDir()) {
               rmdir($path->__toString());
            } else {
               unlink($path->__toString());
            }
        }
        @rmdir($directory);
    }

    /**
     * @covers Symfony_Component_Config_Resource_DirectoryResource::getResource
     */
    public function testGetResource()
    {
        $resource = new Symfony_Component_Config_Resource_DirectoryResource($this->directory);
        $this->assertEquals($this->directory, $resource->getResource(), '->getResource() returns the path to the resource');
    }

    public function testGetPattern()
    {
        $resource = new Symfony_Component_Config_Resource_DirectoryResource('foo', 'bar');
        $this->assertEquals('bar', $resource->getPattern());
    }

    /**
     * @covers Symfony_Component_Config_Resource_DirectoryResource::isFresh
     */
    public function testIsFresh()
    {
        $resource = new Symfony_Component_Config_Resource_DirectoryResource($this->directory);
        // https://bugs.php.net/40568
        $this->assertTrue($resource->isFresh(time() + 10) || $resource->isFresh(time() + 3610), '->isFresh() returns true if the resource has not changed');
        $this->assertFalse($resource->isFresh(time() - 86400), '->isFresh() returns false if the resource has been updated');

        $resource = new Symfony_Component_Config_Resource_DirectoryResource('/____foo/foobar'.rand(1, 999999));
        $this->assertFalse($resource->isFresh(time()), '->isFresh() returns false if the resource does not exist');
    }

    /**
     * @covers Symfony_Component_Config_Resource_DirectoryResource::isFresh
     */
    public function testIsFreshUpdateFile()
    {
        $resource = new Symfony_Component_Config_Resource_DirectoryResource($this->directory);
        touch($this->directory.'/tmp.xml', time() + 20);
        $this->assertFalse($resource->isFresh(time() + 10), '->isFresh() returns false if an existing file is modified');
    }

    /**
     * @covers Symfony_Component_Config_Resource_DirectoryResource::isFresh
     */
    public function testIsFreshNewFile()
    {
        $resource = new Symfony_Component_Config_Resource_DirectoryResource($this->directory);
        touch($this->directory.'/new.xml', time() + 20);
        $this->assertFalse($resource->isFresh(time() + 10), '->isFresh() returns false if a new file is added');
    }

    /**
     * @covers Symfony_Component_Config_Resource_DirectoryResource::isFresh
     */
    public function testIsFreshDeleteFile()
    {
        $resource = new Symfony_Component_Config_Resource_DirectoryResource($this->directory);
        unlink($this->directory.'/tmp.xml');
        $this->assertFalse($resource->isFresh(time()), '->isFresh() returns false if an existing file is removed');
    }

    /**
     * @covers Symfony_Component_Config_Resource_DirectoryResource::isFresh
     */
    public function testIsFreshDeleteDirectory()
    {
        $resource = new Symfony_Component_Config_Resource_DirectoryResource($this->directory);
        $this->removeDirectory($this->directory);
        $this->assertFalse($resource->isFresh(time()), '->isFresh() returns false if the whole resource is removed');
    }

    /**
     * @covers Symfony_Component_Config_Resource_DirectoryResource::isFresh
     */
    public function testIsFreshCreateFileInSubdirectory()
    {
        $subdirectory = $this->directory.'/subdirectory';
        mkdir($subdirectory);

        $resource = new Symfony_Component_Config_Resource_DirectoryResource($this->directory);
        // https://bugs.php.net/40568
        $this->assertTrue($resource->isFresh(time() + 10) || $resource->isFresh(time() + 3610), '->isFresh() returns true if an unmodified subdirectory exists');

        touch($subdirectory.'/newfile.xml', time() + 20);
        $this->assertFalse($resource->isFresh(time() + 10), '->isFresh() returns false if a new file in a subdirectory is added');
    }

    /**
     * @covers Symfony_Component_Config_Resource_DirectoryResource::isFresh
     */
    public function testIsFreshModifySubdirectory()
    {
        $resource = new Symfony_Component_Config_Resource_DirectoryResource($this->directory);

        $subdirectory = $this->directory.'/subdirectory';
        mkdir($subdirectory);
        @touch($subdirectory, time() + 20);

        $this->assertFalse($resource->isFresh(time() + 10), '->isFresh() returns false if a subdirectory is modified (e.g. a file gets deleted)');
    }

    /**
     * @covers Symfony_Component_Config_Resource_DirectoryResource::isFresh
     */
    public function testFilterRegexListNoMatch()
    {
        $resource = new Symfony_Component_Config_Resource_DirectoryResource($this->directory, '/\.(foo|xml)$/');

        touch($this->directory.'/new.bar', time() + 20);
        $this->assertTrue($resource->isFresh(time() + 10) || $resource->isFresh(time() + 3610), '->isFresh() returns true if a new file not matching the filter regex is created');
    }

    /**
     * @covers Symfony_Component_Config_Resource_DirectoryResource::isFresh
     */
    public function testFilterRegexListMatch()
    {
        $resource = new Symfony_Component_Config_Resource_DirectoryResource($this->directory, '/\.(foo|xml)$/');

        touch($this->directory.'/new.xml', time() + 20);
        $this->assertFalse($resource->isFresh(time() + 10), '->isFresh() returns false if an new file matching the filter regex is created ');
    }
}
