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
 * Adds services tagged kernel.fragment_renderer as HTTP content rendering strategies.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_FragmentRendererPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('fragment.handler')) {
            return;
        }

        $definition = $container->getDefinition('fragment.handler');
        foreach (array_keys($container->findTaggedServiceIds('kernel.fragment_renderer')) as $id) {
            // We must assume that the class value has been correctly filled, even if the service is created by a factory
            $class = $container->getDefinition($id)->getClass();

            $refClass = new ReflectionClass($class);
            $interface = 'Symfony_Component_HttpKernel_Fragment_FragmentRendererInterface';
            if (!$refClass->implementsInterface($interface)) {
                throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, $interface));
            }

            $definition->addMethodCall('addRenderer', array(new Symfony_Component_DependencyInjection_Reference($id)));
        }
    }
}
