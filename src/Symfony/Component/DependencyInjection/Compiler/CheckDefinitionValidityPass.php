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
 * This pass validates each definition individually only taking the information
 * into account which is contained in the definition itself.
 *
 * Later passes can rely on the following, and specifically do not need to
 * perform these checks themselves:
 *
 * - non synthetic, non abstract services always have a class set
 * - synthetic services are always public
 * - synthetic services are always of non-prototype scope
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_DependencyInjection_Compiler_CheckDefinitionValidityPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    /**
     * Processes the ContainerBuilder to validate the Definition.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container
     *
     * @throws Symfony_Component_DependencyInjection_Exception_RuntimeException When the Definition is invalid
     */
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            // synthetic service is public
            if ($definition->isSynthetic() && !$definition->isPublic()) {
                throw new Symfony_Component_DependencyInjection_Exception_RuntimeException(sprintf(
                    'A synthetic service ("%s") must be public.',
                    $id
                ));
            }

            // synthetic service has non-prototype scope
            if ($definition->isSynthetic() && Symfony_Component_DependencyInjection_ContainerInterface::SCOPE_PROTOTYPE === $definition->getScope()) {
                throw new Symfony_Component_DependencyInjection_Exception_RuntimeException(sprintf(
                    'A synthetic service ("%s") cannot be of scope "prototype".',
                    $id
                ));
            }

            // non-synthetic, non-abstract service has class
            if (!$definition->isAbstract() && !$definition->isSynthetic() && !$definition->getClass()) {
                if ($definition->getFactoryClass() || $definition->getFactoryService()) {
                    throw new Symfony_Component_DependencyInjection_Exception_RuntimeException(sprintf(
                        'Please add the class to service "%s" even if it is constructed by a factory '
                       .'since we might need to add method calls based on compile-time checks.',
                       $id
                    ));
                }

                throw new Symfony_Component_DependencyInjection_Exception_RuntimeException(sprintf(
                    'The definition for "%s" has no class. If you intend to inject '
                   .'this service dynamically at runtime, please mark it as synthetic=true. '
                   .'If this is an abstract definition solely used by child definitions, '
                   .'please add abstract=true, otherwise specify a class to get rid of this error.',
                   $id
                ));
            }
        }
    }
}
