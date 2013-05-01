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
 * Run this pass before passes that need to know more about the relation of
 * your services.
 *
 * This class will populate the ServiceReferenceGraph with information. You can
 * retrieve the graph in other passes from the compiler.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_DependencyInjection_Compiler_AnalyzeServiceReferencesPass implements Symfony_Component_DependencyInjection_Compiler_RepeatablePassInterface
{
    private $graph;
    private $container;
    private $currentId;
    private $currentDefinition;
    private $repeatedPass;
    private $onlyConstructorArguments;

    /**
     * Constructor.
     *
     * @param Boolean $onlyConstructorArguments Sets this Service Reference pass to ignore method calls
     */
    public function __construct($onlyConstructorArguments = false)
    {
        $this->onlyConstructorArguments = (Boolean) $onlyConstructorArguments;
    }

    /**
     * {@inheritDoc}
     */
    public function setRepeatedPass(Symfony_Component_DependencyInjection_Compiler_RepeatedPass $repeatedPass)
    {
        $this->repeatedPass = $repeatedPass;
    }

    /**
     * Processes a ContainerBuilder object to populate the service reference graph.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container
     */
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $this->container = $container;
        $this->graph     = $container->getCompiler()->getServiceReferenceGraph();
        $this->graph->clear();

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->isSynthetic() || $definition->isAbstract()) {
                continue;
            }

            $this->currentId = $id;
            $this->currentDefinition = $definition;
            $this->processArguments($definition->getArguments());

            if (!$this->onlyConstructorArguments) {
                $this->processArguments($definition->getMethodCalls());
                $this->processArguments($definition->getProperties());
                if ($definition->getConfigurator()) {
                    $this->processArguments(array($definition->getConfigurator()));
                }
            }
        }

        foreach ($container->getAliases() as $id => $alias) {
            $this->graph->connect($id, $alias, (string) $alias->__toString(), $this->getDefinition((string) $alias->__toString()), null);
        }
    }

    /**
     * Processes service definitions for arguments to find relationships for the service graph.
     *
     * @param array $arguments An array of Reference or Definition objects relating to service definitions
     */
    private function processArguments(array $arguments)
    {
        foreach ($arguments as $argument) {
            if (is_array($argument)) {
                $this->processArguments($argument);
            } elseif ($argument instanceof Symfony_Component_DependencyInjection_Reference) {
                $this->graph->connect(
                    $this->currentId,
                    $this->currentDefinition,
                    $this->getDefinitionId((string) $argument->__toString()),
                    $this->getDefinition((string) $argument->__toString()),
                    $argument
                );
            } elseif ($argument instanceof Symfony_Component_DependencyInjection_Definition) {
                $this->processArguments($argument->getArguments());
                $this->processArguments($argument->getMethodCalls());
                $this->processArguments($argument->getProperties());
            }
        }
    }

    /**
     * Returns a service definition given the full name or an alias.
     *
     * @param string $id A full id or alias for a service definition.
     *
     * @return Symfony_Component_DependencyInjection_Definition|null The definition related to the supplied id
     */
    private function getDefinition($id)
    {
        $id = $this->getDefinitionId($id);

        return null === $id ? null : $this->container->getDefinition($id);
    }

    private function getDefinitionId($id)
    {
        while ($this->container->hasAlias($id)) {
            $id = (string) $this->container->getAlias($id)->__toString();
        }

        if (!$this->container->hasDefinition($id)) {
            return null;
        }

        return $id;
    }
}
