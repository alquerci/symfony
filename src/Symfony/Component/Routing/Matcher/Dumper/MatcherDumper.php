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
 * MatcherDumper is the abstract class for all built-in matcher dumpers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Symfony_Component_Routing_Matcher_Dumper_MatcherDumper implements Symfony_Component_Routing_Matcher_Dumper_MatcherDumperInterface
{
    /**
     * @var Symfony_Component_Routing_RouteCollection
     */
    private $routes;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Routing_RouteCollection $routes The RouteCollection to dump
     */
    public function __construct(Symfony_Component_Routing_RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
