<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Templating_Tests_TemplateNameParserTest extends PHPUnit_Framework_TestCase
{
    protected $parser;

    protected function setUp()
    {
        $this->parser = new Symfony_Component_Templating_TemplateNameParser();
    }

    protected function tearDown()
    {
        $this->parser = null;
    }

    /**
     * @dataProvider getLogicalNameToTemplateProvider
     */
    public function testParse($name, $ref)
    {
        $template = $this->parser->parse($name);

        $this->assertEquals($template->getLogicalName(), $ref->getLogicalName());
        $this->assertEquals($template->getLogicalName(), $name);
    }

    public function getLogicalNameToTemplateProvider()
    {
        return array(
            array('/path/to/section/name.engine', new Symfony_Component_Templating_TemplateReference('/path/to/section/name.engine', 'engine')),
            array('name.engine', new Symfony_Component_Templating_TemplateReference('name.engine', 'engine')),
            array('name', new Symfony_Component_Templating_TemplateReference('name')),
        );
    }
}
