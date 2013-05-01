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
 * Replaces aliases with actual service definitions, effectively removing these
 * aliases.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_DependencyInjection_Compiler_ReplaceAliasByActualDefinitionPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    private $compiler;
    private $formatter;
    private $sourceId;

    /**
     * Process the Container to replace aliases with service definitions.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container
     *
     * @throws Symfony_Component_DependencyInjection_Exception_InvalidArgumentException if the service definition does not exist
     */
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $this->compiler = $container->getCompiler();
        $this->formatter = $this->compiler->getLoggingFormatter();

        foreach ($container->getAliases() as $id => $alias) {
            $aliasId = (string) $alias->__toString();

            try {
                $definition = $container->getDefinition($aliasId);
            } catch (Symfony_Component_DependencyInjection_Exception_InvalidArgumentException $e) {
                throw new Symfony_Component_DependencyInjection_Exception_InvalidArgumentException(sprintf('Unable to replace alias "%s" with "%s".', $alias, $id), null, $e);
            }

            if ($definition->isPublic()) {
                continue;
            }

            $definition->setPublic(true);
            $container->setDefinition($id, $definition);
            $container->removeDefinition($aliasId);

            $this->updateReferences($container, $aliasId, $id);

            // we have to restart the process due to concurrent modification of
            // the container
            $this->process($container);

            break;
        }
    }

    /**
     * Updates references to remove aliases.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container The container
     * @param string           $currentId The alias identifier being replaced
     * @param string           $newId     The id of the service the alias points to
     */
    private function updateReferences($container, $currentId, $newId)
    {
        foreach ($container->getAliases() as $id => $alias) {
            if ($currentId === (string) $alias->__toString()) {
                $container->setAlias($id, $newId);
            }
        }

        foreach ($container->getDefinitions() as $id => $definition) {
            $this->sourceId = $id;

            $definition->setArguments(
                $this->updateArgumentReferences($definition->getArguments(), $currentId, $newId)
            );

            $definition->setMethodCalls(
                $this->updateArgumentReferences($definition->getMethodCalls(), $currentId, $newId)
            );

            $definition->setProperties(
                $this->updateArgumentReferences($definition->getProperties(), $currentId, $newId)
            );
        }
    }

    /**
     * Updates argument references.
     *
     * @param array  $arguments An array of Arguments
     * @param string $currentId The alias identifier
     * @param string $newId     The identifier the alias points to
     *
     * @return array
     */
    private function updateArgumentReferences(array $arguments, $currentId, $newId)
    {
        foreach ($arguments as $k => $argument) {
            if (is_array($argument)) {
                $arguments[$k] = $this->updateArgumentReferences($argument, $currentId, $newId);
            } elseif ($argument instanceof Symfony_Component_DependencyInjection_Reference) {
                if ($currentId === (string) $argument->__toString()) {
                    $arguments[$k] = new Symfony_Component_DependencyInjection_Reference($newId, $argument->getInvalidBehavior());
                    $this->compiler->addLogMessage($this->formatter->formatUpdateReference($this, $this->sourceId, $currentId, $newId));
                }
            }
        }

        return $arguments;
    }
}
