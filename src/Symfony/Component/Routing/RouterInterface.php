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
 * RouterInterface is the interface that all Router classes must implement.
 *
 * This interface is the concatenation of UrlMatcherInterface and UrlGeneratorInterface.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Symfony_Component_Routing_RouterInterface extends Symfony_Component_Routing_Matcher_UrlMatcherInterface, Symfony_Component_Routing_Generator_UrlGeneratorInterface
{
    /**
     * Gets the RouteCollection instance associated with this Router.
     *
     * @return Symfony_Component_Routing_RouteCollection A RouteCollection instance
     */
    public function getRouteCollection();
}
