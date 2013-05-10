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
 * Registers the cache warmers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_AddCacheWarmerPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        if (!$container->hasDefinition('cache_warmer')) {
            return;
        }

        $warmers = array();
        foreach ($container->findTaggedServiceIds('kernel.cache_warmer') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $warmers[$priority][] = new Symfony_Component_DependencyInjection_Reference($id);
        }

        if (empty($warmers)) {
            return;
        }

        // sort by priority and flatten
        krsort($warmers);
        $warmers = call_user_func_array('array_merge', $warmers);

        $container->getDefinition('cache_warmer')->replaceArgument(0, $warmers);
    }
}
