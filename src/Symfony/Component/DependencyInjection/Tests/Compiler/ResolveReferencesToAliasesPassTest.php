<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_Compiler_ResolveReferencesToAliasesPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->setAlias('bar', 'foo');
        $def = $container
            ->register('moo')
            ->setArguments(array(new Symfony_Component_DependencyInjection_Reference('bar')))
        ;

        $this->process($container);

        $arguments = $def->getArguments();
        $this->assertEquals('foo', (string) $arguments[0]->__toString());
    }

    public function testProcessRecursively()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->setAlias('bar', 'foo');
        $container->setAlias('moo', 'bar');
        $def = $container
            ->register('foobar')
            ->setArguments(array(new Symfony_Component_DependencyInjection_Reference('moo')))
        ;

        $this->process($container);

        $arguments = $def->getArguments();
        $this->assertEquals('foo', (string) $arguments[0]->__toString());
    }

    protected function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $pass = new Symfony_Component_DependencyInjection_Compiler_ResolveReferencesToAliasesPass();
        $pass->process($container);
    }
}
