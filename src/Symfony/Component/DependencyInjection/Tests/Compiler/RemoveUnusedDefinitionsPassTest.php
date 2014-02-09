<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_Compiler_RemoveUnusedDefinitionsPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container
            ->register('foo')
            ->setPublic(false)
        ;
        $container
            ->register('bar')
            ->setPublic(false)
        ;
        $container
            ->register('moo')
            ->setArguments(array(new Symfony_Component_DependencyInjection_Reference('bar')))
        ;

        $this->process($container);

        $this->assertFalse($container->hasDefinition('foo'));
        $this->assertTrue($container->hasDefinition('bar'));
        $this->assertTrue($container->hasDefinition('moo'));
    }

    public function testProcessRemovesUnusedDefinitionsRecursively()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container
            ->register('foo')
            ->setPublic(false)
        ;
        $container
            ->register('bar')
            ->setArguments(array(new Symfony_Component_DependencyInjection_Reference('foo')))
            ->setPublic(false)
        ;

        $this->process($container);

        $this->assertFalse($container->hasDefinition('foo'));
        $this->assertFalse($container->hasDefinition('bar'));
    }

    public function testProcessWorksWithInlinedDefinitions()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container
            ->register('foo')
            ->setPublic(false)
        ;
        $container
            ->register('bar')
            ->setArguments(array(new Symfony_Component_DependencyInjection_Definition(null, array(new Symfony_Component_DependencyInjection_Reference('foo')))))
        ;

        $this->process($container);

        $this->assertTrue($container->hasDefinition('foo'));
        $this->assertTrue($container->hasDefinition('bar'));
    }

    protected function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $repeatedPass = new Symfony_Component_DependencyInjection_Compiler_RepeatedPass(array(new Symfony_Component_DependencyInjection_Compiler_AnalyzeServiceReferencesPass(), new Symfony_Component_DependencyInjection_Compiler_RemoveUnusedDefinitionsPass()));
        $repeatedPass->process($container);
    }
}
