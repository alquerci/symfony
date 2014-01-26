<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_CacheWarmer_TemplateFinderTest extends Symfony_Bundle_FrameworkBundle_Tests_TestCase
{
    public function testFindAllTemplates()
    {
        $kernel = $this->getMock('Symfony_Component_HttpKernel_Kernel', array(), array(), '', false);

        $kernel
            ->expects($this->any())
            ->method('getBundle')
        ;

        $kernel
            ->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array('BaseBundle' => new Symfony_Bundle_FrameworkBundle_Tests_Fixtures_BaseBundle_BaseBundle())))
        ;

        $parser = new Symfony_Bundle_FrameworkBundle_Templating_TemplateFilenameParser($kernel);

        $finder = new Symfony_Bundle_FrameworkBundle_CacheWarmer_TemplateFinder($kernel, $parser, dirname(__FILE__).'/../Fixtures/Resources');

        $templates = array_map(
            create_function('$template', 'return $template->getLogicalName();'),
            $finder->findAllTemplates()
        );

        $this->assertEquals(6, count($templates), '->findAllTemplates() find all templates in the bundles and global folders');
        $this->assertContains('BaseBundle::base.format.engine', $templates);
        $this->assertContains('BaseBundle::this.is.a.template.format.engine', $templates);
        $this->assertContains('BaseBundle:controller:base.format.engine', $templates);
        $this->assertContains('::this.is.a.template.format.engine', $templates);
        $this->assertContains('::resource.format.engine', $templates);
    }

}
