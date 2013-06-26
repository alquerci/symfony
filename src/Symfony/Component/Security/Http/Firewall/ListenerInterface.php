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
 * Interface that must be implemented by firewall listeners
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface Symfony_Component_Security_Http_Firewall_ListenerInterface
{
    /**
     * This interface must be implemented by firewall listeners.
     *
     * @param Symfony_Component_HttpKernel_Event_GetResponseEvent $event
     */
    public function handle(Symfony_Component_HttpKernel_Event_GetResponseEvent $event);
}
