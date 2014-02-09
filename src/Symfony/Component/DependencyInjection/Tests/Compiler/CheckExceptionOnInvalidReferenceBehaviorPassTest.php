<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_Compiler_CheckExceptionOnInvalidReferenceBehaviorPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();

        $container
            ->register('a', 'stdClass')
            ->addArgument(new Symfony_Component_DependencyInjection_Reference('b'))
        ;
        $container->register('b', 'stdClass');
    }

    /**
     * @expectedException Symfony_Component_DependencyInjection_Exception_ServiceNotFoundException
     */
    public function testProcessThrowsExceptionOnInvalidReference()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();

        $container
            ->register('a', 'stdClass')
            ->addArgument(new Symfony_Component_DependencyInjection_Reference('b'))
        ;

        $this->process($container);
    }

    /**
     * @expectedException Symfony_Component_DependencyInjection_Exception_ServiceNotFoundException
     */
    public function testProcessThrowsExceptionOnInvalidReferenceFromInlinedDefinition()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();

        $def = new Symfony_Component_DependencyInjection_Definition();
        $def->addArgument(new Symfony_Component_DependencyInjection_Reference('b'));

        $container
            ->register('a', 'stdClass')
            ->addArgument($def)
        ;

        $this->process($container);
    }

    private function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $pass = new Symfony_Component_DependencyInjection_Compiler_CheckExceptionOnInvalidReferenceBehaviorPass();
        $pass->process($container);
    }
}
