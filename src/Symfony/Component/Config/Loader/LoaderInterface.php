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
 * LoaderInterface is the interface implemented by all loader classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Symfony_Component_Config_Loader_LoaderInterface
{
    /**
     * Loads a resource.
     *
     * @param mixed  $resource The resource
     * @param string $type     The resource type
     */
    public function load($resource, $type = null);

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null);

    /**
     * Gets the loader resolver.
     *
     * @return Symfony_Component_Config_Loader_LoaderResolverInterface A LoaderResolverInterface instance
     */
    public function getResolver();

    /**
     * Sets the loader resolver.
     *
     * @param Symfony_Component_Config_Loader_LoaderResolverInterface $resolver A LoaderResolverInterface instance
     */
    public function setResolver(Symfony_Component_Config_Loader_LoaderResolverInterface $resolver);

}
