<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Templating_TemplateReferenceTest extends Symfony_Bundle_FrameworkBundle_Tests_TestCase
{
    public function testGetPathWorksWithNamespacedControllers()
    {
        $reference = new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('AcmeBlogBundle', 'Admin\\Post', 'index', 'html', 'twig');

        $this->assertSame(
            '@AcmeBlogBundle/Resources/views/Admin/Post/index.html.twig',
            $reference->getPath()
        );
    }
}
