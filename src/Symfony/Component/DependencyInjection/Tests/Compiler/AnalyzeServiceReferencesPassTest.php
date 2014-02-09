<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_Compiler_AnalyzeServiceReferencesPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();

        $a = $container
            ->register('a')
            ->addArgument($ref1 = new Symfony_Component_DependencyInjection_Reference('b'))
        ;

        $b = $container
            ->register('b')
            ->addMethodCall('setA', array($ref2 = new Symfony_Component_DependencyInjection_Reference('a')))
        ;

        $c = $container
            ->register('c')
            ->addArgument($ref3 = new Symfony_Component_DependencyInjection_Reference('a'))
            ->addArgument($ref4 = new Symfony_Component_DependencyInjection_Reference('b'))
        ;

        $d = $container
            ->register('d')
            ->setProperty('foo', $ref5 = new Symfony_Component_DependencyInjection_Reference('b'))
        ;

        $e = $container
            ->register('e')
            ->setConfigurator(array($ref6 = new Symfony_Component_DependencyInjection_Reference('b'), 'methodName'))
        ;

        $graph = $this->process($container);

        $this->assertCount(4, $edges = $graph->getNode('b')->getInEdges());

        $this->assertSame($ref1, $edges[0]->getValue());
        $this->assertSame($ref4, $edges[1]->getValue());
        $this->assertSame($ref5, $edges[2]->getValue());
        $this->assertSame($ref6, $edges[3]->getValue());
    }

    public function testProcessDetectsReferencesFromInlinedDefinitions()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();

        $container
            ->register('a')
        ;

        $container
            ->register('b')
            ->addArgument(new Symfony_Component_DependencyInjection_Definition(null, array($ref = new Symfony_Component_DependencyInjection_Reference('a'))))
        ;

        $graph = $this->process($container);

        $this->assertCount(1, $refs = $graph->getNode('a')->getInEdges());
        $this->assertSame($ref, $refs[0]->getValue());
    }

    public function testProcessDoesNotSaveDuplicateReferences()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();

        $container
            ->register('a')
        ;
        $container
            ->register('b')
            ->addArgument(new Symfony_Component_DependencyInjection_Definition(null, array($ref1 = new Symfony_Component_DependencyInjection_Reference('a'))))
            ->addArgument(new Symfony_Component_DependencyInjection_Definition(null, array($ref2 = new Symfony_Component_DependencyInjection_Reference('a'))))
        ;

        $graph = $this->process($container);

        $this->assertCount(2, $graph->getNode('a')->getInEdges());
    }

    protected function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $pass = new Symfony_Component_DependencyInjection_Compiler_RepeatedPass(array(new Symfony_Component_DependencyInjection_Compiler_AnalyzeServiceReferencesPass()));
        $pass->process($container);

        return $container->getCompiler()->getServiceReferenceGraph();
    }
}
