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
 * LoaderResolver selects a loader for a given resource.
 *
 * A resource can be anything (e.g. a full path to a config file or a Closure).
 * Each loader determines whether it can load a resource and how.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Config_Loader_LoaderResolver implements Symfony_Component_Config_Loader_LoaderResolverInterface
{
    /**
     * @var Symfony_Component_Config_Loader_LoaderInterface[] An array of LoaderInterface objects
     */
    private $loaders;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Config_Loader_LoaderInterface[] $loaders An array of loaders
     */
    public function __construct(array $loaders = array())
    {
        $this->loaders = array();
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    /**
     * Returns a loader able to load the resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Symfony_Component_Config_Loader_LoaderInterface|false A LoaderInterface instance
     */
    public function resolve($resource, $type = null)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($resource, $type)) {
                return $loader;
            }
        }

        return false;
    }

    /**
     * Adds a loader.
     *
     * @param Symfony_Component_Config_Loader_LoaderInterface $loader A LoaderInterface instance
     */
    public function addLoader(Symfony_Component_Config_Loader_LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
        $loader->setResolver($this);
    }

    /**
     * Returns the registered loaders.
     *
     * @return Symfony_Component_Config_Loader_LoaderInterface[] An array of LoaderInterface instances
     */
    public function getLoaders()
    {
        return $this->loaders;
    }
}
