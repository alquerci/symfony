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
 * Checks that all references are pointing to a valid service.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_DependencyInjection_Compiler_CheckExceptionOnInvalidReferenceBehaviorPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    private $container;
    private $sourceId;

    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $this->container = $container;

        foreach ($container->getDefinitions() as $id => $definition) {
            $this->sourceId = $id;
            $this->processDefinition($definition);
        }
    }

    private function processDefinition(Symfony_Component_DependencyInjection_Definition $definition)
    {
        $this->processReferences($definition->getArguments());
        $this->processReferences($definition->getMethodCalls());
        $this->processReferences($definition->getProperties());
    }

    private function processReferences(array $arguments)
    {
        foreach ($arguments as $argument) {
            if (is_array($argument)) {
                $this->processReferences($argument);
            } elseif ($argument instanceof Symfony_Component_DependencyInjection_Definition) {
                $this->processDefinition($argument);
            } elseif ($argument instanceof Symfony_Component_DependencyInjection_Reference && Symfony_Component_DependencyInjection_ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE === $argument->getInvalidBehavior()) {
                $destId = (string) $argument->__toString();

                if (!$this->container->has($destId)) {
                    throw new Symfony_Component_DependencyInjection_Exception_ServiceNotFoundException($destId, $this->sourceId);
                }
            }
        }
    }
}
