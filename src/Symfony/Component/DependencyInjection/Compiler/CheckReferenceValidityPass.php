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
 * Checks the validity of references
 *
 * The following checks are performed by this pass:
 * - target definitions are not abstract
 * - target definitions are of equal or wider scope
 * - target definitions are in the same scope hierarchy
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_DependencyInjection_Compiler_CheckReferenceValidityPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    private $container;
    private $currentId;
    private $currentDefinition;
    private $currentScope;
    private $currentScopeAncestors;
    private $currentScopeChildren;

    /**
     * Processes the ContainerBuilder to validate References.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container
     */
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $this->container = $container;

        $children = $this->container->getScopeChildren();
        $ancestors = array();

        $scopes = $this->container->getScopes();
        foreach ($scopes as $name => $parent) {
            $ancestors[$name] = array($parent);

            while (isset($scopes[$parent])) {
                $ancestors[$name][] = $parent = $scopes[$parent];
            }
        }

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->isSynthetic() || $definition->isAbstract()) {
                continue;
            }

            $this->currentId = $id;
            $this->currentDefinition = $definition;
            $this->currentScope = $scope = $definition->getScope();

            if (Symfony_Component_DependencyInjection_ContainerInterface::SCOPE_CONTAINER === $scope) {
                $this->currentScopeChildren = array_keys($scopes);
                $this->currentScopeAncestors = array();
            } elseif (Symfony_Component_DependencyInjection_ContainerInterface::SCOPE_PROTOTYPE !== $scope) {
                $this->currentScopeChildren = $children[$scope];
                $this->currentScopeAncestors = $ancestors[$scope];
            }

            $this->validateReferences($definition->getArguments());
            $this->validateReferences($definition->getMethodCalls());
            $this->validateReferences($definition->getProperties());
        }
    }

    /**
     * Validates an array of References.
     *
     * @param array $arguments An array of Reference objects
     *
     * @throws Symfony_Component_DependencyInjection_Exception_RuntimeException when there is a reference to an abstract definition.
     */
    private function validateReferences(array $arguments)
    {
        foreach ($arguments as $argument) {
            if (is_array($argument)) {
                $this->validateReferences($argument);
            } elseif ($argument instanceof Symfony_Component_DependencyInjection_Reference) {
                $targetDefinition = $this->getDefinition((string) $argument->__toString());

                if (null !== $targetDefinition && $targetDefinition->isAbstract()) {
                    throw new Symfony_Component_DependencyInjection_Exception_RuntimeException(sprintf(
                        'The definition "%s" has a reference to an abstract definition "%s". '
                       .'Abstract definitions cannot be the target of references.',
                       $this->currentId,
                       $argument->__toString()
                    ));
                }

                $this->validateScope($argument, $targetDefinition);
            }
        }
    }

    /**
     * Validates the scope of a single Reference.
     *
     * @param Symfony_Component_DependencyInjection_Reference  $reference
     * @param Symfony_Component_DependencyInjection_Definition $definition
     *
     * @throws Symfony_Component_DependencyInjection_Exception_ScopeWideningInjectionException when the definition references a service of a narrower scope
     * @throws Symfony_Component_DependencyInjection_Exception_ScopeCrossingInjectionException when the definition references a service of another scope hierarchy
     */
    private function validateScope(Symfony_Component_DependencyInjection_Reference $reference, Symfony_Component_DependencyInjection_Definition $definition = null)
    {
        if (Symfony_Component_DependencyInjection_ContainerInterface::SCOPE_PROTOTYPE === $this->currentScope) {
            return;
        }

        if (!$reference->isStrict()) {
            return;
        }

        if (null === $definition) {
            return;
        }

        if ($this->currentScope === $scope = $definition->getScope()) {
            return;
        }

        $id = (string) $reference->__toString();

        if (in_array($scope, $this->currentScopeChildren, true)) {
            throw new Symfony_Component_DependencyInjection_Exception_ScopeWideningInjectionException($this->currentId, $this->currentScope, $id, $scope);
        }

        if (!in_array($scope, $this->currentScopeAncestors, true)) {
            throw new Symfony_Component_DependencyInjection_Exception_ScopeCrossingInjectionException($this->currentId, $this->currentScope, $id, $scope);
        }
    }

    /**
     * Returns the Definition given an id.
     *
     * @param string $id Definition identifier
     *
     * @return Symfony_Component_DependencyInjection_Definition
     */
    private function getDefinition($id)
    {
        if (!$this->container->hasDefinition($id)) {
            return null;
        }

        return $this->container->getDefinition($id);
    }
}
