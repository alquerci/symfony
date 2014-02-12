<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Templating_Tests_PhpEngineTest extends PHPUnit_Framework_TestCase
{
    protected $loader;

    protected function setUp()
    {
        $this->loader = new Symfony_Component_Templating_Tests_ProjectTemplateLoader();
    }

    protected function tearDown()
    {
        $this->loader = null;
    }

    public function testConstructor()
    {
        $engine = new Symfony_Component_Templating_Tests_ProjectTemplateEngine(new Symfony_Component_Templating_TemplateNameParser(), $this->loader);
        $this->assertEquals($this->loader, $engine->getLoader(), '__construct() takes a loader instance as its second first argument');
    }

    public function testOffsetGet()
    {
        $engine = new Symfony_Component_Templating_Tests_ProjectTemplateEngine(new Symfony_Component_Templating_TemplateNameParser(), $this->loader);
        $engine->set($helper = new Symfony_Component_Templating_Tests_Fixtures_SimpleHelper('bar'), 'foo');
        $this->assertEquals($helper, $engine['foo'], '->offsetGet() returns the value of a helper');

        try {
            $engine['bar'];
            $this->fail('->offsetGet() throws an InvalidArgumentException if the helper is not defined');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->offsetGet() throws an InvalidArgumentException if the helper is not defined');
            $this->assertEquals('The helper "bar" is not defined.', $e->getMessage(), '->offsetGet() throws an InvalidArgumentException if the helper is not defined');
        }
    }

    public function testGetSetHas()
    {
        $engine = new Symfony_Component_Templating_Tests_ProjectTemplateEngine(new Symfony_Component_Templating_TemplateNameParser(), $this->loader);
        $foo = new Symfony_Component_Templating_Tests_Fixtures_SimpleHelper('foo');
        $engine->set($foo);
        $this->assertEquals($foo, $engine->get('foo'), '->set() sets a helper');

        $engine[$foo] = 'bar';
        $this->assertEquals($foo, $engine->get('bar'), '->set() takes an alias as a second argument');

        $this->assertTrue(isset($engine['bar']));

        try {
            $engine->get('foobar');
            $this->fail('->get() throws an InvalidArgumentException if the helper is not defined');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->get() throws an InvalidArgumentException if the helper is not defined');
            $this->assertEquals('The helper "foobar" is not defined.', $e->getMessage(), '->get() throws an InvalidArgumentException if the helper is not defined');
        }

        $this->assertTrue(isset($engine['bar']));
        $this->assertTrue($engine->has('foo'), '->has() returns true if the helper exists');
        $this->assertFalse($engine->has('foobar'), '->has() returns false if the helper does not exist');
    }

    public function testUnsetHelper()
    {
        $engine = new Symfony_Component_Templating_Tests_ProjectTemplateEngine(new Symfony_Component_Templating_TemplateNameParser(), $this->loader);
        $foo = new Symfony_Component_Templating_Tests_Fixtures_SimpleHelper('foo');
        $engine->set($foo);

        $this->setExpectedException('LogicException');

        unset($engine['foo']);
    }

    public function testExtendRender()
    {
        $engine = new Symfony_Component_Templating_Tests_ProjectTemplateEngine(new Symfony_Component_Templating_TemplateNameParser(), $this->loader, array(), array(new Symfony_Component_Templating_Helper_SlotsHelper()));
        try {
            $engine->render('name');
            $this->fail('->render() throws an InvalidArgumentException if the template does not exist');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '->render() throws an InvalidArgumentException if the template does not exist');
            $this->assertEquals('The template "name" does not exist.', $e->getMessage(), '->render() throws an InvalidArgumentException if the template does not exist');
        }

        $engine = new Symfony_Component_Templating_Tests_ProjectTemplateEngine(new Symfony_Component_Templating_TemplateNameParser(), $this->loader, array(new Symfony_Component_Templating_Helper_SlotsHelper()));
        $engine->set(new Symfony_Component_Templating_Tests_Fixtures_SimpleHelper('bar'));
        $this->loader->setTemplate('foo.php', '<?php $view->extend("layout.php"); echo $view[\'foo\'], $foo ?>');
        $this->loader->setTemplate('layout.php', '-<?php echo $view[\'slots\']->get("_content") ?>-');
        $this->assertEquals('-barfoo-', $engine->render('foo.php', array('foo' => 'foo')), '->render() uses the decorator to decorate the template');

        $engine = new Symfony_Component_Templating_Tests_ProjectTemplateEngine(new Symfony_Component_Templating_TemplateNameParser(), $this->loader, array(new Symfony_Component_Templating_Helper_SlotsHelper()));
        $engine->set(new Symfony_Component_Templating_Tests_Fixtures_SimpleHelper('bar'));
        $this->loader->setTemplate('bar.php', 'bar');
        $this->loader->setTemplate('foo.php', '<?php $view->extend("layout.php"); echo $foo ?>');
        $this->loader->setTemplate('layout.php', '<?php echo $view->render("bar.php") ?>-<?php echo $view[\'slots\']->get("_content") ?>-');
        $this->assertEquals('bar-foo-', $engine->render('foo.php', array('foo' => 'foo', 'bar' => 'bar')), '->render() supports render() calls in templates');
    }

    public function testEscape()
    {
        $engine = new Symfony_Component_Templating_Tests_ProjectTemplateEngine(new Symfony_Component_Templating_TemplateNameParser(), $this->loader);
        $this->assertEquals('&lt;br /&gt;', $engine->escape('<br />'), '->escape() escapes strings');
        $foo = new stdClass();
        $this->assertEquals($foo, $engine->escape($foo), '->escape() does nothing on non strings');
    }

    public function testGetSetCharset()
    {
        $engine = new Symfony_Component_Templating_Tests_ProjectTemplateEngine(new Symfony_Component_Templating_TemplateNameParser(), $this->loader);
        $this->assertEquals('UTF-8', $engine->getCharset(), '->getCharset() returns UTF-8 by default');
        $engine->setCharset('ISO-8859-1');
        $this->assertEquals('ISO-8859-1', $engine->getCharset(), '->setCharset() changes the default charset to use');
    }

    public function testGlobalVariables()
    {
        $engine = new Symfony_Component_Templating_Tests_ProjectTemplateEngine(new Symfony_Component_Templating_TemplateNameParser(), $this->loader);
        $engine->addGlobal('global_variable', 'lorem ipsum');

        $this->assertEquals(array(
            'global_variable' => 'lorem ipsum',
        ), $engine->getGlobals());
    }

    public function testGlobalsGetPassedToTemplate()
    {
        $engine = new Symfony_Component_Templating_Tests_ProjectTemplateEngine(new Symfony_Component_Templating_TemplateNameParser(), $this->loader);
        $engine->addGlobal('global', 'global variable');

        $this->loader->setTemplate('global.php', '<?php echo $global; ?>');

        $this->assertEquals($engine->render('global.php'), 'global variable');

        $this->assertEquals($engine->render('global.php', array('global' => 'overwritten')), 'overwritten');
    }

    public function testGetLoader()
    {
        $engine = new Symfony_Component_Templating_Tests_ProjectTemplateEngine(new Symfony_Component_Templating_TemplateNameParser(), $this->loader);

        $this->assertSame($this->loader, $engine->getLoader());
    }
}

class Symfony_Component_Templating_Tests_ProjectTemplateEngine extends Symfony_Component_Templating_PhpEngine
{
    public function getLoader()
    {
        return $this->loader;
    }
}

class Symfony_Component_Templating_Tests_ProjectTemplateLoader extends Symfony_Component_Templating_Loader_Loader
{
    public $templates = array();

    public function setTemplate($name, $content)
    {
        $template = new Symfony_Component_Templating_TemplateReference($name, 'php');
        $this->templates[$template->getLogicalName()] = $content;
    }

    public function load(Symfony_Component_Templating_TemplateReferenceInterface $template)
    {
        if (isset($this->templates[$template->getLogicalName()])) {
            return new Symfony_Component_Templating_Storage_StringStorage($this->templates[$template->getLogicalName()]);
        }

        return false;
    }

    public function isFresh(Symfony_Component_Templating_TemplateReferenceInterface $template, $time)
    {
        return false;
    }
}
