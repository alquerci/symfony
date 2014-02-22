<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// should probably be moved to the Translation component
class Symfony_Bundle_FrameworkBundle_Tests_Templating_Helper_FormHelperDivLayoutTest extends Symfony_Component_Form_Tests_AbstractDivLayoutTest
{
    /**
     * @var Symfony_Component_Templating_PhpEngine
     */
    protected $engine;

    protected function setUp()
    {
        if (!class_exists('Symfony_Bundle_FrameworkBundle_Templating_Helper_TranslatorHelper')) {
            $this->markTestSkipped('The "FrameworkBundle" is not available');
        }

        if (!class_exists('Symfony_Component_Templating_PhpEngine')) {
            $this->markTestSkipped('The "Templating" component is not available');
        }

        parent::setUp();
    }

    protected function getExtensions()
    {
        // should be moved to the Form component once absolute file paths are supported
        // by the default name parser in the Templating component
        $reflClass = new ReflectionClass('Symfony_Bundle_FrameworkBundle_FrameworkBundle');
        $root = realpath(dirname($reflClass->getFileName()) . '/Resources/views');
        $rootTheme = realpath(dirname(__FILE__).'/Resources');
        $templateNameParser = new Symfony_Bundle_FrameworkBundle_Tests_Templating_Helper_Fixtures_StubTemplateNameParser($root, $rootTheme);
        $loader = new Symfony_Component_Templating_Loader_FilesystemLoader(array());

        $this->engine = new Symfony_Component_Templating_PhpEngine($templateNameParser, $loader);
        $this->engine->addGlobal('global', '');
        $this->engine->setHelpers(array(
            new Symfony_Bundle_FrameworkBundle_Templating_Helper_TranslatorHelper(new Symfony_Bundle_FrameworkBundle_Tests_Templating_Helper_Fixtures_StubTranslator()),
        ));

        return array_merge(parent::getExtensions(), array(
            new Symfony_Component_Form_Extension_Templating_TemplatingExtension($this->engine, $this->csrfProvider, array(
                'FrameworkBundle:Form',
            )),
        ));
    }

    protected function tearDown()
    {
        $this->engine = null;

        parent::tearDown();
    }

    protected function renderEnctype(Symfony_Component_Form_FormView $view)
    {
        return (string) $this->engine->get('form')->enctype($view);
    }

    protected function renderLabel(Symfony_Component_Form_FormView $view, $label = null, array $vars = array())
    {
        return (string) $this->engine->get('form')->label($view, $label, $vars);
    }

    protected function renderErrors(Symfony_Component_Form_FormView $view)
    {
        return (string) $this->engine->get('form')->errors($view);
    }

    protected function renderWidget(Symfony_Component_Form_FormView $view, array $vars = array())
    {
        return (string) $this->engine->get('form')->widget($view, $vars);
    }

    protected function renderRow(Symfony_Component_Form_FormView $view, array $vars = array())
    {
        return (string) $this->engine->get('form')->row($view, $vars);
    }

    protected function renderRest(Symfony_Component_Form_FormView $view, array $vars = array())
    {
        return (string) $this->engine->get('form')->rest($view, $vars);
    }

    protected function setTheme(Symfony_Component_Form_FormView $view, array $themes)
    {
        $this->engine->get('form')->setTheme($view, $themes);
    }

    public static function themeBlockInheritanceProvider()
    {
        return array(
            array(array('TestBundle:Parent'))
        );
    }

    public static function themeInheritanceProvider()
    {
        return array(
            array(array('TestBundle:Parent'), array('TestBundle:Child'))
        );
    }
}
