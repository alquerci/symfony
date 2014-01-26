<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Templating_TemplateNameParserTest extends Symfony_Bundle_FrameworkBundle_Tests_TestCase
{
    protected $parser;

    protected function setUp()
    {
        $kernel = $this->getMock('Symfony_Component_HttpKernel_KernelInterface');
        $kernel
            ->expects($this->any())
            ->method('getBundle')
            ->will($this->returnCallback(create_function('$bundle', '
                if (in_array($bundle, array(\'SensioFooBundle\', \'SensioCmsFooBundle\', \'FooBundle\'))) {
                    return true;
                }

                throw new InvalidArgumentException();
            ')))
        ;
        $this->parser = new Symfony_Bundle_FrameworkBundle_Templating_TemplateNameParser($kernel);
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
            array('FooBundle:Post:index.html.php', new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('FooBundle', 'Post', 'index', 'html', 'php')),
            array('FooBundle:Post:index.html.twig', new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('FooBundle', 'Post', 'index', 'html', 'twig')),
            array('FooBundle:Post:index.xml.php', new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('FooBundle', 'Post', 'index', 'xml', 'php')),
            array('SensioFooBundle:Post:index.html.php', new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('SensioFooBundle', 'Post', 'index', 'html', 'php')),
            array('SensioCmsFooBundle:Post:index.html.php', new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('SensioCmsFooBundle', 'Post', 'index', 'html', 'php')),
            array(':Post:index.html.php', new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('', 'Post', 'index', 'html', 'php')),
            array('::index.html.php', new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('', '', 'index', 'html', 'php')),
            array('FooBundle:Post:foo.bar.index.html.php', new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('FooBundle', 'Post', 'foo.bar.index', 'html', 'php')),
        );
    }

    /**
     * @dataProvider      getInvalidLogicalNameProvider
     * @expectedException InvalidArgumentException
     */
    public function testParseInvalidName($name)
    {
        $this->parser->parse($name);
    }

    public function getInvalidLogicalNameProvider()
    {
        return array(
            array('BarBundle:Post:index.html.php'),
            array('FooBundle:Post:index'),
            array('FooBundle:Post'),
            array('FooBundle:Post:foo:bar'),
        );
    }
}
