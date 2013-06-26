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
 * Adds all configured security voters to the access decision manager
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Bundle_SecurityBundle_DependencyInjection_Compiler_AddSecurityVotersPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        if (!$container->hasDefinition('security.access.decision_manager')) {
            return;
        }

        $voters = new SplPriorityQueue();
        foreach ($container->findTaggedServiceIds('security.voter') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $voters->insert(new Symfony_Component_DependencyInjection_Reference($id), $priority);
        }

        $voters = iterator_to_array($voters);
        ksort($voters);

        $container->getDefinition('security.access.decision_manager')->replaceArgument(0, array_values($voters));
    }
}
