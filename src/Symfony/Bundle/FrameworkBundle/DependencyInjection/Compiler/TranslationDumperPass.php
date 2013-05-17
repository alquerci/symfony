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
 * Adds tagged translation.formatter services to translation writer
 */
class Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_TranslationDumperPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        if (!$container->hasDefinition('translation.writer')) {
            return;
        }

        $definition = $container->getDefinition('translation.writer');

        foreach ($container->findTaggedServiceIds('translation.dumper') as $id => $attributes) {
            $definition->addMethodCall('addDumper', array($attributes[0]['alias'], new Symfony_Component_DependencyInjection_Reference($id)));
        }
    }
}
