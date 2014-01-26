<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_DependencyInjection_Compiler_FragmentRendererPassTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests that content rendering not implementing FragmentRendererInterface
     * trigger an exception.
     *
     * @expectedException InvalidArgumentException
     */
    public function testContentRendererWithoutInterface()
    {
        // one service, not implementing any interface
        $services = array(
            'my_content_renderer' => array(),
        );

        $definition = $this->getMock('Symfony_Component_DependencyInjection_Definition');
        $definition->expects($this->atLeastOnce())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $builder = $this->getMock('Symfony_Component_DependencyInjection_ContainerBuilder');
        $builder->expects($this->any())
            ->method('hasDefinition')
            ->will($this->returnValue(true));

        // We don't test kernel.fragment_renderer here
        $builder->expects($this->atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will($this->returnValue($services));

        $builder->expects($this->atLeastOnce())
            ->method('getDefinition')
            ->will($this->returnValue($definition));

        $pass = new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_FragmentRendererPass();
        $pass->process($builder);
    }

    public function testValidContentRenderer()
    {
        $services = array(
            'my_content_renderer' => array(),
        );

        $renderer = $this->getMock('Symfony_Component_DependencyInjection_Definition');
        $renderer
            ->expects($this->once())
            ->method('addMethodCall')
            ->with('addRenderer', array(new Symfony_Component_DependencyInjection_Reference('my_content_renderer')))
        ;

        $definition = $this->getMock('Symfony_Component_DependencyInjection_Definition');
        $definition->expects($this->atLeastOnce())
            ->method('getClass')
            ->will($this->returnValue('Symfony_Bundle_FrameworkBundle_Tests_DependencyInjection_Compiler_RendererService'));

        $builder = $this->getMock('Symfony_Component_DependencyInjection_ContainerBuilder');
        $builder->expects($this->any())
            ->method('hasDefinition')
            ->will($this->returnValue(true));

        // We don't test kernel.fragment_renderer here
        $builder->expects($this->atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will($this->returnValue($services));

        $builder->expects($this->atLeastOnce())
            ->method('getDefinition')
            ->will($this->onConsecutiveCalls($renderer, $definition));

        $pass = new Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_FragmentRendererPass();
        $pass->process($builder);
    }
}

class Symfony_Bundle_FrameworkBundle_Tests_DependencyInjection_Compiler_RendererService implements Symfony_Component_HttpKernel_Fragment_FragmentRendererInterface
{
    public function render($uri, Symfony_Component_HttpFoundation_Request $request, array $options = array())
    {
    }

    public function getName()
    {
        return 'test';
    }
}
