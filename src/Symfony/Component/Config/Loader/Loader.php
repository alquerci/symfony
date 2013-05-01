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
 * Loader is the abstract class used by all built-in loaders.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Symfony_Component_Config_Loader_Loader implements Symfony_Component_Config_Loader_LoaderInterface
{
    protected $resolver;

    /**
     * Gets the loader resolver.
     *
     * @return Symfony_Component_Config_Loader_LoaderResolverInterface A LoaderResolverInterface instance
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * Sets the loader resolver.
     *
     * @param Symfony_Component_Config_Loader_LoaderResolverInterface $resolver A LoaderResolverInterface instance
     */
    public function setResolver(Symfony_Component_Config_Loader_LoaderResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Imports a resource.
     *
     * @param mixed  $resource A Resource
     * @param string $type     The resource type
     *
     * @return mixed
     */
    public function import($resource, $type = null)
    {
        return $this->resolve($resource)->load($resource, $type);
    }

    /**
     * Finds a loader able to load an imported resource.
     *
     * @param mixed  $resource A Resource
     * @param string $type     The resource type
     *
     * @return Symfony_Component_Config_Loader_LoaderInterface A LoaderInterface instance
     *
     * @throws Symfony_Component_Config_Exception_FileLoaderLoadException if no loader is found
     */
    public function resolve($resource, $type = null)
    {
        if ($this->supports($resource, $type)) {
            return $this;
        }

        $loader = null === $this->resolver ? false : $this->resolver->resolve($resource, $type);

        if (false === $loader) {
            throw new Symfony_Component_Config_Exception_FileLoaderLoadException($resource);
        }

        return $loader;
    }
}
