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
 * RequestMatcherInterface is the interface that all request matcher classes must implement.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Symfony_Component_Routing_Matcher_RequestMatcherInterface
{
    /**
     * Tries to match a request with a set of routes.
     *
     * If the matcher can not find information, it must throw one of the exceptions documented
     * below.
     *
     * @param Symfony_Component_HttpFoundation_Request $request The request to match
     *
     * @return array An array of parameters
     *
     * @throws Symfony_Component_Routing_Exception_ResourceNotFoundException If no matching resource could be found
     * @throws Symfony_Component_Routing_Exception_MethodNotAllowedException If a matching resource was found but the request method is not allowed
     */
    public function matchRequest(Symfony_Component_HttpFoundation_Request $request);
}
