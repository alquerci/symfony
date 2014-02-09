<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This class tests the integration of the different compiler passes
 */
class Symfony_Component_DependencyInjection_Tests_Compiler_IntegrationTest extends PHPUnit_Framework_TestCase
{
    /**
     * This tests that the following dependencies are correctly processed:
     *
     * A is public, B/C are private
     * A -> C
     * B -> C
     */
    public function testProcessRemovesAndInlinesRecursively()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->setResourceTracking(false);

        $a = $container
            ->register('a', 'stdClass')
            ->addArgument(new Symfony_Component_DependencyInjection_Reference('c'))
        ;

        $b = $container
            ->register('b', 'stdClass')
            ->addArgument(new Symfony_Component_DependencyInjection_Reference('c'))
            ->setPublic(false)
        ;

        $c = $container
            ->register('c', 'stdClass')
            ->setPublic(false)
        ;

        $container->compile();

        $this->assertTrue($container->hasDefinition('a'));
        $arguments = $a->getArguments();
        $this->assertSame($c, $arguments[0]);
        $this->assertFalse($container->hasDefinition('b'));
        $this->assertFalse($container->hasDefinition('c'));
    }

    public function testProcessInlinesReferencesToAliases()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->setResourceTracking(false);

        $a = $container
            ->register('a', 'stdClass')
            ->addArgument(new Symfony_Component_DependencyInjection_Reference('b'))
        ;

        $container->setAlias('b', new Symfony_Component_DependencyInjection_Alias('c', false));

        $c = $container
            ->register('c', 'stdClass')
            ->setPublic(false)
        ;

        $container->compile();

        $this->assertTrue($container->hasDefinition('a'));
        $arguments = $a->getArguments();
        $this->assertSame($c, $arguments[0]);
        $this->assertFalse($container->hasAlias('b'));
        $this->assertFalse($container->hasDefinition('c'));
    }

    public function testProcessInlinesWhenThereAreMultipleReferencesButFromTheSameDefinition()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->setResourceTracking(false);

        $container
            ->register('a', 'stdClass')
            ->addArgument(new Symfony_Component_DependencyInjection_Reference('b'))
            ->addMethodCall('setC', array(new Symfony_Component_DependencyInjection_Reference('c')))
        ;

        $container
            ->register('b', 'stdClass')
            ->addArgument(new Symfony_Component_DependencyInjection_Reference('c'))
            ->setPublic(false)
        ;

        $container
            ->register('c', 'stdClass')
            ->setPublic(false)
        ;

        $container->compile();

        $this->assertTrue($container->hasDefinition('a'));
        $this->assertFalse($container->hasDefinition('b'));
        $this->assertFalse($container->hasDefinition('c'), 'Service C was not inlined.');
    }
}
