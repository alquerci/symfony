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
 * ClosureLoader loads service definitions from a PHP closure.
 *
 * The Closure has access to the container as its first argument.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_DependencyInjection_Loader_ClosureLoader extends Symfony_Component_Config_Loader_Loader
{
    private $container;

    /**
     * Constructor.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container A ContainerBuilder instance
     */
    public function __construct(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * Loads a Closure.
     *
     * @param callable $closure The resource
     * @param string   $type    The resource type
     */
    public function load($closure, $type = null)
    {
        call_user_func($closure, $this->container);
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
        return is_callable($resource);
    }
}
