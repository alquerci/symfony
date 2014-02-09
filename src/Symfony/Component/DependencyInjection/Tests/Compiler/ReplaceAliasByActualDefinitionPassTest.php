<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_Compiler_ReplaceAliasByActualDefinitionPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();

        $container->register('a', 'stdClass');

        $bDefinition = new Symfony_Component_DependencyInjection_Definition('stdClass');
        $bDefinition->setPublic(false);
        $container->setDefinition('b', $bDefinition);

        $container->setAlias('a_alias', 'a');
        $container->setAlias('b_alias', 'b');

        $this->process($container);

        $this->assertTrue($container->has('a'), '->process() does nothing to public definitions.');
        $this->assertTrue($container->hasAlias('a_alias'));
        $this->assertFalse($container->has('b'), '->process() removes non-public definitions.');
        $this->assertTrue(
            $container->has('b_alias') && !$container->hasAlias('b_alias'),
            '->process() replaces alias to actual.'
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testProcessWithInvalidAlias()
    {
        $container = new Symfony_Component_DependencyInjection_ContainerBuilder();
        $container->setAlias('a_alias', 'a');
        $this->process($container);
    }

    protected function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $pass = new Symfony_Component_DependencyInjection_Compiler_ReplaceAliasByActualDefinitionPass();
        $pass->process($container);
    }
}
