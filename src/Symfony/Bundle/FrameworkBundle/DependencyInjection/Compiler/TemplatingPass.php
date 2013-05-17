<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_TemplatingPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        if ($container->hasDefinition('templating')) {
            return;
        }

        if ($container->hasDefinition('templating.engine.php')) {
            $helpers = array();
            foreach ($container->findTaggedServiceIds('templating.helper') as $id => $attributes) {
                if (isset($attributes[0]['alias'])) {
                    $helpers[$attributes[0]['alias']] = $id;
                }
            }

            if (count($helpers) > 0) {
                $definition = $container->getDefinition('templating.engine.php');
                $definition->addMethodCall('setHelpers', array($helpers));
            }
        }
    }
}
