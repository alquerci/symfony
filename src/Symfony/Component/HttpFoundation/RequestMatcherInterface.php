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
 * RequestMatcherInterface is an interface for strategies to match a Request.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface Symfony_Component_HttpFoundation_RequestMatcherInterface
{
    /**
     * Decides whether the rule(s) implemented by the strategy matches the supplied request.
     *
     * @param Symfony_Component_HttpFoundation_Request $request The request to check for a match
     *
     * @return Boolean true if the request matches, false otherwise
     *
     * @api
     */
    public function matches(Symfony_Component_HttpFoundation_Request $request);
}
