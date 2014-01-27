<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Config_Tests_FileLocatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getIsAbsolutePathTests
     */
    public function testIsAbsolutePath($path)
    {
        if (version_compare(phpversion(), '5.3.2', '<')) {
            $this->markTestSkipped('Require PHP >= 5.3.2');
        }

        $loader = new Symfony_Component_Config_FileLocator(array());
        $r = new ReflectionObject($loader);
        $m = $r->getMethod('isAbsolutePath');
        $m->setAccessible(true);

        $this->assertTrue($m->invoke($loader, $path), '->isAbsolutePath() returns true for an absolute path');
    }

    public function getIsAbsolutePathTests()
    {
        return array(
            array('/foo.xml'),
            array('c:\\\\foo.xml'),
            array('c:/foo.xml'),
            array('\\server\\foo.xml'),
            array('https://server/foo.xml'),
            array('phar://server/foo.xml'),
        );
    }

    public function testLocate()
    {
        $loader = new Symfony_Component_Config_FileLocator(dirname(__FILE__).'/Fixtures');

        $this->assertEquals(
            dirname(__FILE__).DIRECTORY_SEPARATOR.'FileLocatorTest.php',
            $loader->locate('FileLocatorTest.php', dirname(__FILE__)),
            '->locate() returns the absolute filename if the file exists in the given path'
        );

        $this->assertEquals(
            dirname(__FILE__).'/Fixtures'.DIRECTORY_SEPARATOR.'foo.xml',
            $loader->locate('foo.xml', dirname(__FILE__)),
            '->locate() returns the absolute filename if the file exists in one of the paths given in the constructor'
        );

        $this->assertEquals(
            dirname(__FILE__).'/Fixtures'.DIRECTORY_SEPARATOR.'foo.xml',
            $loader->locate(dirname(__FILE__).'/Fixtures'.DIRECTORY_SEPARATOR.'foo.xml', dirname(__FILE__)),
            '->locate() returns the absolute filename if the file exists in one of the paths given in the constructor'
        );

        $loader = new Symfony_Component_Config_FileLocator(array(dirname(__FILE__).'/Fixtures', dirname(__FILE__).'/Fixtures/Again'));

        $this->assertEquals(
            array(dirname(__FILE__).'/Fixtures'.DIRECTORY_SEPARATOR.'foo.xml', dirname(__FILE__).'/Fixtures/Again'.DIRECTORY_SEPARATOR.'foo.xml'),
            $loader->locate('foo.xml', dirname(__FILE__), false),
            '->locate() returns an array of absolute filenames'
        );

        $this->assertEquals(
            array(dirname(__FILE__).'/Fixtures'.DIRECTORY_SEPARATOR.'foo.xml', dirname(__FILE__).'/Fixtures/Again'.DIRECTORY_SEPARATOR.'foo.xml'),
            $loader->locate('foo.xml', dirname(__FILE__).'/Fixtures', false),
            '->locate() returns an array of absolute filenames'
        );

        $loader = new Symfony_Component_Config_FileLocator(dirname(__FILE__).'/Fixtures/Again');

        $this->assertEquals(
            array(dirname(__FILE__).'/Fixtures'.DIRECTORY_SEPARATOR.'foo.xml', dirname(__FILE__).'/Fixtures/Again'.DIRECTORY_SEPARATOR.'foo.xml'),
            $loader->locate('foo.xml', dirname(__FILE__).'/Fixtures', false),
            '->locate() returns an array of absolute filenames'
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLocateThrowsAnExceptionIfTheFileDoesNotExists()
    {
        $loader = new Symfony_Component_Config_FileLocator(array(dirname(__FILE__).'/Fixtures'));

        $loader->locate('foobar.xml', dirname(__FILE__));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLocateThrowsAnExceptionIfTheFileDoesNotExistsInAbsolutePath()
    {
        $loader = new Symfony_Component_Config_FileLocator(array(dirname(__FILE__).'/Fixtures'));

        $loader->locate(dirname(__FILE__).'/Fixtures/foobar.xml', dirname(__FILE__));
    }
}
