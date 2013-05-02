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
 * @api
 */
interface Symfony_Component_Routing_RequestContextAwareInterface
{
    /**
     * Sets the request context.
     *
     * @param Symfony_Component_Routing_RequestContext $context The context
     *
     * @api
     */
    public function setContext(Symfony_Component_Routing_RequestContext $context);

    /**
     * Gets the request context.
     *
     * @return Symfony_Component_Routing_RequestContext The context
     *
     * @api
     */
    public function getContext();
}
