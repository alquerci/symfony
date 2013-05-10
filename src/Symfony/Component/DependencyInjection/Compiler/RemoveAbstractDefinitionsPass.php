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
 * Removes abstract Definitions
 *
 */
class Symfony_Component_DependencyInjection_Compiler_RemoveAbstractDefinitionsPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    /**
     * Removes abstract definitions from the ContainerBuilder
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container
     */
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $compiler = $container->getCompiler();
        $formatter = $compiler->getLoggingFormatter();

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->isAbstract()) {
                $container->removeDefinition($id);
                $compiler->addLogMessage($formatter->formatRemoveService($this, $id, 'abstract'));
            }
        }
    }
}
