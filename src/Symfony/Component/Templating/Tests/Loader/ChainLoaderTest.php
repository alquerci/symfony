<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Templating_Tests_Loader_ChainLoaderTest extends PHPUnit_Framework_TestCase
{
    protected $loader1;
    protected $loader2;

    protected function setUp()
    {
        $fixturesPath = realpath(dirname(__FILE__).'/../Fixtures/');
        $this->loader1 = new Symfony_Component_Templating_Loader_FilesystemLoader($fixturesPath.'/null/%name%');
        $this->loader2 = new Symfony_Component_Templating_Loader_FilesystemLoader($fixturesPath.'/templates/%name%');
    }

    public function testConstructor()
    {
        $loader = new Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader1(array($this->loader1, $this->loader2));
        $this->assertEquals(array($this->loader1, $this->loader2), $loader->getLoaders(), '__construct() takes an array of template loaders as its second argument');
    }

    public function testAddLoader()
    {
        $loader = new Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader1(array($this->loader1));
        $loader->addLoader($this->loader2);
        $this->assertEquals(array($this->loader1, $this->loader2), $loader->getLoaders(), '->addLoader() adds a template loader at the end of the loaders');
    }

    public function testLoad()
    {
        $loader = new Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader1(array($this->loader1, $this->loader2));
        $this->assertFalse($loader->load(new Symfony_Component_Templating_TemplateReference('bar', 'php')), '->load() returns false if the template is not found');
        $this->assertFalse($loader->load(new Symfony_Component_Templating_TemplateReference('foo', 'php')), '->load() returns false if the template does not exists for the given renderer');
        $this->assertInstanceOf(
            'Symfony_Component_Templating_Storage_FileStorage',
            $loader->load(new Symfony_Component_Templating_TemplateReference('foo.php', 'php')),
            '->load() returns a Symfony_Component_Templating_Storage_FileStorage if the template exists'
        );
    }
}

class Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader1 extends Symfony_Component_Templating_Loader_ChainLoader
{
    public function getLoaders()
    {
        return $this->loaders;
    }
}
