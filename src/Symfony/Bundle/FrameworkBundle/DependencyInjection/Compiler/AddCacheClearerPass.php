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
 * Registers the cache clearers.
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
class Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_AddCacheClearerPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        if (!$container->hasDefinition('cache_clearer')) {
            return;
        }

        $clearers = array();
        foreach ($container->findTaggedServiceIds('kernel.cache_clearer') as $id => $attributes) {
            $clearers[] = new Symfony_Component_DependencyInjection_Reference($id);
        }

        $container->getDefinition('cache_clearer')->replaceArgument(0, $clearers);
    }
}
