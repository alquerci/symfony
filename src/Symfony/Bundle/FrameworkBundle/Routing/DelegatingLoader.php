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
 * DelegatingLoader delegates route loading to other loaders using a loader resolver.
 *
 * This implementation resolves the _controller attribute from the short notation
 * to the fully-qualified form (from a:b:c to class:method).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Routing_DelegatingLoader extends Symfony_Component_Config_Loader_DelegatingLoader
{
    protected $parser;
    protected $logger;

    /**
     * Constructor.
     *
     * @param Symfony_Bundle_FrameworkBundle_Controller_ControllerNameParser    $parser   A ControllerNameParser instance
     * @param Psr_Log_LoggerInterface         $logger   A LoggerInterface instance
     * @param Symfony_Component_Config_Loader_LoaderResolverInterface $resolver A LoaderResolverInterface instance
     */
    public function __construct(Symfony_Bundle_FrameworkBundle_Controller_ControllerNameParser $parser, Psr_Log_LoggerInterface $logger = null, Symfony_Component_Config_Loader_LoaderResolverInterface $resolver)
    {
        $this->parser = $parser;
        $this->logger = $logger;

        parent::__construct($resolver);
    }

    /**
     * Loads a resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Symfony_Component_Routing_RouteCollection A RouteCollection instance
     */
    public function load($resource, $type = null)
    {
        $collection = parent::load($resource, $type);

        foreach ($collection->all() as $route) {
            if ($controller = $route->getDefault('_controller')) {
                try {
                    $controller = $this->parser->parse($controller);
                } catch (Exception $e) {
                    // unable to optimize unknown notation
                }

                $route->setDefault('_controller', $controller);
            }
        }

        return $collection;
    }
}
