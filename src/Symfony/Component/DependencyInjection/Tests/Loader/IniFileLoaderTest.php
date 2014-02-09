<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_Loader_IniFileLoaderTest extends PHPUnit_Framework_TestCase
{
    protected static $fixturesPath;

    protected $container;
    protected $loader;

    public static function setUpBeforeClass()
    {
        self::$fixturesPath = realpath(dirname(__FILE__).'/../Fixtures/');
    }

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_Config_Loader_Loader')) {
            $this->markTestSkipped('The "Config" component is not available');
        }

        $this->container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $this->loader    = new Symfony_Component_DependencyInjection_Loader_IniFileLoader($this->container, new Symfony_Component_Config_FileLocator(self::$fixturesPath.'/ini'));
    }

    /**
     * @covers Symfony_Component_DependencyInjection_Loader_IniFileLoader::__construct
     * @covers Symfony_Component_DependencyInjection_Loader_IniFileLoader::load
     */
    public function testIniFileCanBeLoaded()
    {
        $this->loader->load('parameters.ini');
        $this->assertEquals(array('foo' => 'bar', 'bar' => '%foo%'), $this->container->getParameterBag()->all(), '->load() takes a single file name as its first argument');
    }

    /**
     * @covers Symfony_Component_DependencyInjection_Loader_IniFileLoader::__construct
     * @covers Symfony_Component_DependencyInjection_Loader_IniFileLoader::load
     */
    public function testExceptionIsRaisedWhenIniFileDoesNotExist()
    {
        try {
            $this->loader->load('foo.ini');
            $this->fail('->load() throws an InvalidArgumentException if the loaded file does not exist');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if the loaded file does not exist');
            $this->assertStringStartsWith('The file "foo.ini" does not exist (in: ', $e->getMessage(), '->load() throws an InvalidArgumentException if the loaded file does not exist');
        }
    }

    /**
     * @covers Symfony_Component_DependencyInjection_Loader_IniFileLoader::__construct
     * @covers Symfony_Component_DependencyInjection_Loader_IniFileLoader::load
     */
    public function testExceptionIsRaisedWhenIniFileCannotBeParsed()
    {
        try {
            @$this->loader->load('nonvalid.ini');
            $this->fail('->load() throws an InvalidArgumentException if the loaded file is not parseable');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if the loaded file is not parseable');
            $this->assertEquals('The "nonvalid.ini" file is not valid.', $e->getMessage(), '->load() throws an InvalidArgumentException if the loaded file is not parseable');
        }
    }

    /**
     * @covers Symfony_Component_DependencyInjection_Loader_IniFileLoader::supports
     */
    public function testSupports()
    {
        $loader = new Symfony_Component_DependencyInjection_Loader_IniFileLoader(new Symfony_Component_DependencyInjection_ContainerBuilder(), new Symfony_Component_Config_FileLocator());

        $this->assertTrue($loader->supports('foo.ini'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');
    }
}
