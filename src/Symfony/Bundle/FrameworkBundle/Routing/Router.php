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
 * This Router creates the Loader only when the cache is empty.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Routing_Router extends Symfony_Component_Routing_Router implements Symfony_Component_HttpKernel_CacheWarmer_WarmableInterface
{
    private $container;

    /**
     * Constructor.
     *
     * @param Symfony_Component_DependencyInjection_ContainerInterface $container A ContainerInterface instance
     * @param mixed              $resource  The main resource to load
     * @param array              $options   An array of options
     * @param Symfony_Component_Routing_RequestContext     $context   The context
     */
    public function __construct(Symfony_Component_DependencyInjection_ContainerInterface $container, $resource, array $options = array(), Symfony_Component_Routing_RequestContext $context = null)
    {
        $this->container = $container;

        $this->resource = $resource;
        $this->context = null === $context ? new Symfony_Component_Routing_RequestContext() : $context;
        $this->setOptions($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        if (null === $this->collection) {
            $this->collection = $this->container->get('routing.loader')->load($this->resource, $this->options['resource_type']);
            $this->resolveParameters($this->collection);
        }

        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $currentDir = $this->getOption('cache_dir');

        // force cache generation
        $this->setOption('cache_dir', $cacheDir);
        $this->getMatcher();
        $this->getGenerator();

        $this->setOption('cache_dir', $currentDir);
    }

    /**
     * Replaces placeholders with service container parameter values in:
     * - the route defaults,
     * - the route requirements,
     * - the route pattern.
     * - the route host.
     *
     * @param Symfony_Component_Routing_RouteCollection $collection
     */
    private function resolveParameters(Symfony_Component_Routing_RouteCollection $collection)
    {
        foreach ($collection as $route) {
            foreach ($route->getDefaults() as $name => $value) {
                $route->setDefault($name, $this->resolve($value));
            }

            foreach ($route->getRequirements() as $name => $value) {
                 $route->setRequirement($name, $this->resolve($value));
            }

            $route->setPath($this->resolve($route->getPath()));
            $route->setHost($this->resolve($route->getHost()));
        }
    }

    /**
     * Recursively replaces placeholders with the service container parameters.
     *
     * @param mixed $value The source which might contain "%placeholders%"
     *
     * @return mixed The source with the placeholders replaced by the container
     *               parameters. Array are resolved recursively.
     *
     * @throws Symfony_Component_DependencyInjection_Exception_ParameterNotFoundException When a placeholder does not exist as a container parameter
     * @throws Symfony_Component_DependencyInjection_Exception_RuntimeException           When a container value is not a string or a numeric value
     */
    private function resolve($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->resolve($val);
            }

            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        $this->_resolveCBvalue = $value;
        $escapedValue = preg_replace_callback('/%%|%([^%\s]+)%/', array($this, '_resolveCB'), $value);

        return str_replace('%%', '%', $escapedValue);
    }
    private $_resolveCBvalue;
    public function _resolveCB($match)
    {
        // skip %%
        if (!isset($match[1])) {
            return '%%';
        }

        $key = strtolower($match[1]);

        if (!$this->container->hasParameter($key)) {
            throw new Symfony_Component_DependencyInjection_Exception_ParameterNotFoundException($key);
        }

        $resolved = $this->container->getParameter($key);

        if (is_string($resolved) || is_numeric($resolved)) {
            return (string) $resolved;
        }

        throw new Symfony_Component_DependencyInjection_Exception_RuntimeException(sprintf(
            'A string value must be composed of strings and/or numbers,' .
            'but found parameter "%s" of type %s inside string value "%s".',
            $key,
            gettype($resolved),
            $this->_resolveCBvalue)
        );
    }
}
