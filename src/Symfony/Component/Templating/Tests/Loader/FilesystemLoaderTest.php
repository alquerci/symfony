<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Templating_Tests_Loader_FilesystemLoaderTest extends PHPUnit_Framework_TestCase
{
    protected static $fixturesPath;

    public static function setUpBeforeClass()
    {
        self::$fixturesPath = realpath(dirname(__FILE__).'/../Fixtures/');
    }

    public function testConstructor()
    {
        $pathPattern = self::$fixturesPath.'/templates/%name%.%engine%';
        $path = self::$fixturesPath.'/templates';
        $loader = new Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader2($pathPattern);
        $this->assertEquals(array($pathPattern), $loader->getTemplatePathPatterns(), '__construct() takes a path as its second argument');
        $loader = new Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader2(array($pathPattern));
        $this->assertEquals(array($pathPattern), $loader->getTemplatePathPatterns(), '__construct() takes an array of paths as its second argument');
    }

    public function testIsAbsolutePath()
    {
        $this->assertTrue(Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader2::isAbsolutePath('/foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
        $this->assertTrue(Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader2::isAbsolutePath('c:\\\\foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
        $this->assertTrue(Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader2::isAbsolutePath('c:/foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
        $this->assertTrue(Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader2::isAbsolutePath('\\server\\foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
        $this->assertTrue(Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader2::isAbsolutePath('https://server/foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
        $this->assertTrue(Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader2::isAbsolutePath('phar://server/foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
    }

    public function testLoad()
    {
        $pathPattern = self::$fixturesPath.'/templates/%name%';
        $path = self::$fixturesPath.'/templates';
        $loader = new Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader2($pathPattern);
        $storage = $loader->load(new Symfony_Component_Templating_TemplateReference($path.'/foo.php', 'php'));
        $this->assertInstanceOf('Symfony_Component_Templating_Storage_FileStorage', $storage, '->load() returns a Symfony_Component_Templating_Storage_FileStorage if you pass an absolute path');
        $this->assertEquals($path.'/foo.php', (string) $storage->__toString(), '->load() returns a Symfony_Component_Templating_Storage_FileStorage pointing to the passed absolute path');

        $this->assertFalse($loader->load(new Symfony_Component_Templating_TemplateReference('bar', 'php')), '->load() returns false if the template is not found');

        $storage = $loader->load(new Symfony_Component_Templating_TemplateReference('foo.php', 'php'));
        $this->assertInstanceOf('Symfony_Component_Templating_Storage_FileStorage', $storage, '->load() returns a Symfony_Component_Templating_Storage_FileStorage if you pass a relative template that exists');
        $this->assertEquals($path.'/foo.php', (string) $storage->__toString(), '->load() returns a Symfony_Component_Templating_Storage_FileStorage pointing to the absolute path of the template');

        $loader = new Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader2($pathPattern);
        $loader->setDebugger($debugger = new Symfony_Component_Templating_Tests_Fixtures_ProjectTemplateDebugger());
        $this->assertFalse($loader->load(new Symfony_Component_Templating_TemplateReference('foo.xml', 'php')), '->load() returns false if the template does not exists for the given engine');
        $this->assertTrue($debugger->hasMessage('Failed loading template'), '->load() logs a "Failed loading template" message if the template is not found');

        $loader = new Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader2(array(self::$fixturesPath.'/null/%name%', $pathPattern));
        $loader->setDebugger($debugger = new Symfony_Component_Templating_Tests_Fixtures_ProjectTemplateDebugger());
        $loader->load(new Symfony_Component_Templating_TemplateReference('foo.php', 'php'));
        $this->assertTrue($debugger->hasMessage('Loaded template file'), '->load() logs a "Loaded template file" message if the template is found');
    }
}

class Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader2 extends Symfony_Component_Templating_Loader_FilesystemLoader
{
    public function getTemplatePathPatterns()
    {
        return $this->templatePathPatterns;
    }

    public static function isAbsolutePath($path)
    {
        return parent::isAbsolutePath($path);
    }
}
