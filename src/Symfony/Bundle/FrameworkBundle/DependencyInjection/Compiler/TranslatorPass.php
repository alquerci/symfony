<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_TranslatorPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        if (!$container->hasDefinition('translator.default')) {
            return;
        }

        $loaders = array();
        foreach ($container->findTaggedServiceIds('translation.loader') as $id => $attributes) {
            $loaders[$id][] = $attributes[0]['alias'];
            if (isset($attributes[0]['legacy-alias'])) {
                $loaders[$id][] = $attributes[0]['legacy-alias'];
            }
        }

        if ($container->hasDefinition('translation.loader')) {
            $definition = $container->getDefinition('translation.loader');
            foreach ($loaders as $id => $formats) {
                foreach ($formats as $format) {
                    $definition->addMethodCall('addLoader', array($format, new Symfony_Component_DependencyInjection_Reference($id)));
                }
            }
        }

        $container->findDefinition('translator.default')->replaceArgument(2, $loaders);
    }
}
