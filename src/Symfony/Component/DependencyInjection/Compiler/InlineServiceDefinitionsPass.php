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
 * Inline service definitions where this is possible.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_DependencyInjection_Compiler_InlineServiceDefinitionsPass implements Symfony_Component_DependencyInjection_Compiler_RepeatablePassInterface
{
    private $repeatedPass;
    private $graph;
    private $compiler;
    private $formatter;
    private $currentId;

    /**
     * {@inheritDoc}
     */
    public function setRepeatedPass(Symfony_Component_DependencyInjection_Compiler_RepeatedPass $repeatedPass)
    {
        $this->repeatedPass = $repeatedPass;
    }

    /**
     * Processes the ContainerBuilder for inline service definitions.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container
     */
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $this->compiler = $container->getCompiler();
        $this->formatter = $this->compiler->getLoggingFormatter();
        $this->graph = $this->compiler->getServiceReferenceGraph();

        foreach ($container->getDefinitions() as $id => $definition) {
            $this->currentId = $id;

            $definition->setArguments(
                $this->inlineArguments($container, $definition->getArguments())
            );

            $definition->setMethodCalls(
                $this->inlineArguments($container, $definition->getMethodCalls())
            );

            $definition->setProperties(
                $this->inlineArguments($container, $definition->getProperties())
            );
        }
    }

    /**
     * Processes inline arguments.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container The ContainerBuilder
     * @param array            $arguments An array of arguments
     *
     * @return array
     */
    private function inlineArguments(Symfony_Component_DependencyInjection_ContainerBuilder $container, array $arguments)
    {
        foreach ($arguments as $k => $argument) {
            if (is_array($argument)) {
                $arguments[$k] = $this->inlineArguments($container, $argument);
            } elseif ($argument instanceof Symfony_Component_DependencyInjection_Reference) {
                if (!$container->hasDefinition($id = (string) $argument->__toString())) {
                    continue;
                }

                if ($this->isInlineableDefinition($container, $id, $definition = $container->getDefinition($id))) {
                    $this->compiler->addLogMessage($this->formatter->formatInlineService($this, $id, $this->currentId));

                    if (Symfony_Component_DependencyInjection_ContainerInterface::SCOPE_PROTOTYPE !== $definition->getScope()) {
                        $arguments[$k] = $definition;
                    } else {
                        $arguments[$k] = clone $definition;
                    }
                }
            } elseif ($argument instanceof Symfony_Component_DependencyInjection_Definition) {
                $argument->setArguments($this->inlineArguments($container, $argument->getArguments()));
                $argument->setMethodCalls($this->inlineArguments($container, $argument->getMethodCalls()));
                $argument->setProperties($this->inlineArguments($container, $argument->getProperties()));
            }
        }

        return $arguments;
    }

    /**
     * Checks if the definition is inlineable.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container
     * @param string           $id
     * @param Symfony_Component_DependencyInjection_Definition       $definition
     *
     * @return Boolean If the definition is inlineable
     */
    private function isInlineableDefinition(Symfony_Component_DependencyInjection_ContainerBuilder $container, $id, Symfony_Component_DependencyInjection_Definition $definition)
    {
        if (Symfony_Component_DependencyInjection_ContainerInterface::SCOPE_PROTOTYPE === $definition->getScope()) {
            return true;
        }

        if ($definition->isPublic()) {
            return false;
        }

        if (!$this->graph->hasNode($id)) {
            return true;
        }

        $ids = array();
        foreach ($this->graph->getNode($id)->getInEdges() as $edge) {
            $ids[] = $edge->getSourceNode()->getId();
        }

        if (count(array_unique($ids)) > 1) {
            return false;
        }

        return $container->getDefinition(reset($ids))->getScope() === $definition->getScope();
    }
}
