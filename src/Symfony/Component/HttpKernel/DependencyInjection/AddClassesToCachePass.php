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
 * Sets the classes to compile in the cache for the container.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_HttpKernel_DependencyInjection_AddClassesToCachePass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    private $kernel;

    public function __construct(Symfony_Component_HttpKernel_Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $classes = array();
        foreach ($container->getExtensions() as $extension) {
            if ($extension instanceof Symfony_Component_HttpKernel_DependencyInjection_Extension) {
                $classes = array_merge($classes, $extension->getClassesToCompile());
            }
        }

        $this->kernel->setClassCache(array_unique($container->getParameterBag()->resolveValue($classes)));
    }
}
