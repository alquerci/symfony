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
 * Emulates the invalid behavior if the reference is not found within the
 * container.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_DependencyInjection_Compiler_ResolveInvalidReferencesPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    private $container;

    /**
     * Process the ContainerBuilder to resolve invalid references.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container
     */
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $this->container = $container;
        foreach ($container->getDefinitions() as $definition) {
            if ($definition->isSynthetic() || $definition->isAbstract()) {
                continue;
            }

            $definition->setArguments(
                $this->processArguments($definition->getArguments())
            );

            $calls = array();
            foreach ($definition->getMethodCalls() as $call) {
                try {
                    $calls[] = array($call[0], $this->processArguments($call[1], true));
                } catch (Symfony_Component_DependencyInjection_Exception_RuntimeException $ignore) {
                    // this call is simply removed
                }
            }
            $definition->setMethodCalls($calls);

            $properties = array();
            foreach ($definition->getProperties() as $name => $value) {
                try {
                    $value = $this->processArguments(array($value), true);
                    $properties[$name] = reset($value);
                } catch (Symfony_Component_DependencyInjection_Exception_RuntimeException $ignore) {
                    // ignore property
                }
            }
            $definition->setProperties($properties);
        }
    }

    /**
     * Processes arguments to determine invalid references.
     *
     * @param array   $arguments    An array of Reference objects
     * @param Boolean $inMethodCall
     *
     * @return array
     *
     * @throws Symfony_Component_DependencyInjection_Exception_RuntimeException When the config is invalid
     */
    private function processArguments(array $arguments, $inMethodCall = false)
    {
        foreach ($arguments as $k => $argument) {
            if (is_array($argument)) {
                $arguments[$k] = $this->processArguments($argument, $inMethodCall);
            } elseif ($argument instanceof Symfony_Component_DependencyInjection_Reference) {
                $id = (string) $argument->__toString();

                $invalidBehavior = $argument->getInvalidBehavior();
                $exists = $this->container->has($id);

                // resolve invalid behavior
                if ($exists && Symfony_Component_DependencyInjection_ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE !== $invalidBehavior) {
                    $arguments[$k] = new Symfony_Component_DependencyInjection_Reference($id, Symfony_Component_DependencyInjection_ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $argument->isStrict());
                } elseif (!$exists && Symfony_Component_DependencyInjection_ContainerInterface::NULL_ON_INVALID_REFERENCE === $invalidBehavior) {
                    $arguments[$k] = null;
                } elseif (!$exists && Symfony_Component_DependencyInjection_ContainerInterface::IGNORE_ON_INVALID_REFERENCE === $invalidBehavior) {
                    if ($inMethodCall) {
                        throw new Symfony_Component_DependencyInjection_Exception_RuntimeException('Method shouldn\'t be called.');
                    }

                    $arguments[$k] = null;
                }
            }
        }

        return $arguments;
    }
}
