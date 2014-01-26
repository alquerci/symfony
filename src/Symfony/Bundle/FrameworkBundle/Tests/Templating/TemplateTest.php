<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Templating_TemplateTest extends Symfony_Bundle_FrameworkBundle_Tests_TestCase
{
    /**
     * @dataProvider getTemplateToPathProvider
     */
    public function testGetPathForTemplatesInABundle($template, $path)
    {
        if ($template->get('bundle')) {
            $this->assertEquals($template->getPath(), $path);
        }
    }

    /**
     * @dataProvider getTemplateToPathProvider
     */
    public function testGetPathForTemplatesOutOfABundle($template, $path)
    {
        if (!$template->get('bundle')) {
            $this->assertEquals($template->getPath(), $path);
        }
    }

    public function getTemplateToPathProvider()
    {
        return array(
            array(new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('FooBundle', 'Post', 'index', 'html', 'php'), '@FooBundle/Resources/views/Post/index.html.php'),
            array(new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('FooBundle', '', 'index', 'html', 'twig'), '@FooBundle/Resources/views/index.html.twig'),
            array(new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('', 'Post', 'index', 'html', 'php'), 'views/Post/index.html.php'),
            array(new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('', '', 'index', 'html', 'php'), 'views/index.html.php'),
        );
    }
}
