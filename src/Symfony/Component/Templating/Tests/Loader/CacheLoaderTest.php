<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Templating_Tests_Loader_CacheLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $loader = new Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader($varLoader = new Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoaderVar(new Symfony_Component_Templating_TemplateNameParser()), sys_get_temp_dir());
        $this->assertTrue($loader->getLoader() === $varLoader, '__construct() takes a template loader as its first argument');
        $this->assertEquals(sys_get_temp_dir(), $loader->getDir(), '__construct() takes a directory where to store the cache as its second argument');
    }

    public function testLoad()
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.rand(111111, 999999);
        mkdir($dir, 0777, true);
        $loader = new Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader($varLoader = new Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoaderVar(new Symfony_Component_Templating_TemplateNameParser()), $dir);
        $loader->setDebugger($debugger = new Symfony_Component_Templating_Tests_Fixtures_ProjectTemplateDebugger());
        $this->assertFalse($loader->load(new Symfony_Component_Templating_TemplateReference('foo', 'php')), '->load() returns false if the embed loader is not able to load the template');
        $loader->load(new Symfony_Component_Templating_TemplateReference('index'));
        $this->assertTrue($debugger->hasMessage('Storing template'), '->load() logs a "Storing template" message if the template is found');
        $loader->load(new Symfony_Component_Templating_TemplateReference('index'));
        $this->assertTrue($debugger->hasMessage('Fetching template'), '->load() logs a "Storing template" message if the template is fetched from cache');
    }
}

class Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader extends Symfony_Component_Templating_Loader_CacheLoader
{
    public function getDir()
    {
        return $this->dir;
    }

    public function getLoader()
    {
        return $this->loader;
    }
}

class Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoaderVar extends Symfony_Component_Templating_Loader_Loader
{
    public function getIndexTemplate()
    {
        return 'Hello World';
    }

    public function getSpecialTemplate()
    {
        return 'Hello {{ name }}';
    }

    public function load(Symfony_Component_Templating_TemplateReferenceInterface $template)
    {
        if (method_exists($this, $method = 'get'.ucfirst($template->get('name')).'Template')) {
            return new Symfony_Component_Templating_Storage_StringStorage($this->$method());
        }

        return false;
    }

    public function isFresh(Symfony_Component_Templating_TemplateReferenceInterface $template, $time)
    {
        return false;
    }
}
