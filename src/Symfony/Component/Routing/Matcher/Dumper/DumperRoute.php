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
 * Container for a Route.
 *
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 */
class Symfony_Component_Routing_Matcher_Dumper_DumperRoute
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Symfony_Component_Routing_Route
     */
    private $route;

    /**
     * Constructor.
     *
     * @param string $name  The route name
     * @param Symfony_Component_Routing_Route  $route The route
     */
    public function __construct($name, Symfony_Component_Routing_Route $route)
    {
        $this->name = $name;
        $this->route = $route;
    }

    /**
     * Returns the route name.
     *
     * @return string The route name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the route.
     *
     * @return Symfony_Component_Routing_Route The route
     */
    public function getRoute()
    {
        return $this->route;
    }
}
