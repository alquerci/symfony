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
 * PhpFileLoader loads service definitions from a PHP file.
 *
 * The PHP file is required and the $container variable can be
 * used form the file to change the container.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_DependencyInjection_Loader_PhpFileLoader extends Symfony_Component_DependencyInjection_Loader_FileLoader
{
    /**
     * Loads a PHP file.
     *
     * @param mixed  $file The resource
     * @param string $type The resource type
     */
    public function load($file, $type = null)
    {
        // the container and loader variables are exposed to the included file below
        $container = $this->container;
        $loader = $this;

        $path = $this->locator->locate($file);
        $this->setCurrentDir(dirname($path));
        $this->container->addResource(new Symfony_Component_Config_Resource_FileResource($path));

        include $path;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}
