<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_Compiler_InlineServiceDefinitionsPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container
            ->register('inlinable.service')
            ->setPublic(false)
        ;

        $container
            ->register('service')
            ->setArguments(array(new Symfony_Component_DependencyInjection_Reference('inlinable.service')))
        ;

        $this->process($container);

        $arguments = $container->getDefinition('service')->getArguments();
        $this->assertInstanceOf('Symfony_Component_DependencyInjection_Definition', $arguments[0]);
        $this->assertSame($container->getDefinition('inlinable.service'), $arguments[0]);
    }

    public function testProcessDoesNotInlineWhenAliasedServiceIsNotOfPrototypeScope()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container
            ->register('foo')
            ->setPublic(false)
        ;
        $container->setAlias('moo', 'foo');

        $container
            ->register('service')
            ->setArguments(array($ref = new Symfony_Component_DependencyInjection_Reference('foo')))
        ;

        $this->process($container);

        $arguments = $container->getDefinition('service')->getArguments();
        $this->assertSame($ref, $arguments[0]);
    }

    public function testProcessDoesInlineServiceOfPrototypeScope()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container
            ->register('foo')
            ->setScope('prototype')
        ;
        $container
            ->register('bar')
            ->setPublic(false)
            ->setScope('prototype')
        ;
        $container->setAlias('moo', 'bar');

        $container
            ->register('service')
            ->setArguments(array(new Symfony_Component_DependencyInjection_Reference('foo'), $ref = new Symfony_Component_DependencyInjection_Reference('moo'), new Symfony_Component_DependencyInjection_Reference('bar')))
        ;

        $this->process($container);

        $arguments = $container->getDefinition('service')->getArguments();
        $this->assertEquals($container->getDefinition('foo'), $arguments[0]);
        $this->assertNotSame($container->getDefinition('foo'), $arguments[0]);
        $this->assertSame($ref, $arguments[1]);
        $this->assertEquals($container->getDefinition('bar'), $arguments[2]);
        $this->assertNotSame($container->getDefinition('bar'), $arguments[2]);
    }

    public function testProcessInlinesIfMultipleReferencesButAllFromTheSameDefinition()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();

        $a = $container->register('a')->setPublic(false);
        $b = $container
            ->register('b')
            ->addArgument(new Symfony_Component_DependencyInjection_Reference('a'))
            ->addArgument(new Symfony_Component_DependencyInjection_Definition(null, array(new Symfony_Component_DependencyInjection_Reference('a'))))
        ;

        $this->process($container);

        $arguments = $b->getArguments();
        $this->assertSame($a, $arguments[0]);

        $inlinedArguments = $arguments[1]->getArguments();
        $this->assertSame($a, $inlinedArguments[0]);
    }

    public function testProcessInlinesOnlyIfSameScope()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();

        $container->addScope(new Symfony_Component_DependencyInjection_Scope('foo'));
        $a = $container->register('a')->setPublic(false)->setScope('foo');
        $b = $container->register('b')->addArgument(new Symfony_Component_DependencyInjection_Reference('a'));

        $this->process($container);
        $arguments = $b->getArguments();
        $this->assertEquals(new Symfony_Component_DependencyInjection_Reference('a'), $arguments[0]);
        $this->assertTrue($container->hasDefinition('a'));
    }

    protected function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $repeatedPass = new Symfony_Component_DependencyInjection_Compiler_RepeatedPass(array(new Symfony_Component_DependencyInjection_Compiler_AnalyzeServiceReferencesPass(), new Symfony_Component_DependencyInjection_Compiler_InlineServiceDefinitionsPass()));
        $repeatedPass->process($container);
    }
}
