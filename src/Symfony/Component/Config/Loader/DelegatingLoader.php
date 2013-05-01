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
 * DelegatingLoader delegates loading to other loaders using a loader resolver.
 *
 * This loader acts as an array of LoaderInterface objects - each having
 * a chance to load a given resource (handled by the resolver)
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Config_Loader_DelegatingLoader extends Symfony_Component_Config_Loader_Loader
{
    /**
     * Constructor.
     *
     * @param Symfony_Component_Config_Loader_LoaderResolverInterface $resolver A LoaderResolverInterface instance
     */
    public function __construct(Symfony_Component_Config_Loader_LoaderResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Loads a resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return mixed
     *
     * @throws Symfony_Component_Config_Exception_FileLoaderLoadException if no loader is found.
     */
    public function load($resource, $type = null)
    {
        if (false === $loader = $this->resolver->resolve($resource, $type)) {
            throw new Symfony_Component_Config_Exception_FileLoaderLoadException($resource);
        }

        return $loader->load($resource, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return false === $this->resolver->resolve($resource, $type) ? false : true;
    }
}
