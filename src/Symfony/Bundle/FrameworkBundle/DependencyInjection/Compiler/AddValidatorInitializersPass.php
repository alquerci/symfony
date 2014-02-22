<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_AddValidatorInitializersPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        if (!$container->hasDefinition('validator')) {
            return;
        }

        $initializers = array();
        foreach ($container->findTaggedServiceIds('validator.initializer') as $id => $attributes) {
            $initializers[] = new Symfony_Component_DependencyInjection_Reference($id);
        }

        $container->getDefinition('validator')->replaceArgument(4, $initializers);
    }
}
