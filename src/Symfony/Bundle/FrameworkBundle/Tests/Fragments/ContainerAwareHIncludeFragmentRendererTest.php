<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Fragment_ContainerAwareHIncludeFragmentRendererTest extends Symfony_Bundle_FrameworkBundle_Tests_TestCase
{
    public function testRender()
    {
        $container = $this->getMock('Symfony_Component_DependencyInjection_ContainerInterface');
        $container->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->getMock('Symfony_Component_Templating_EngineInterface')))
        ;
        $renderer = new Symfony_Bundle_FrameworkBundle_Fragment_ContainerAwareHIncludeFragmentRenderer($container);
        $renderer->render('/', Symfony_Component_HttpFoundation_Request::create('/'));
    }
}
