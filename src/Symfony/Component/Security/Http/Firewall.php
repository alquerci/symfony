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
 * Firewall uses a FirewallMap to register security listeners for the given
 * request.
 *
 * It allows for different security strategies within the same application
 * (a Basic authentication for the /api, and a web based authentication for
 * everything else for instance).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Http_Firewall implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    private $map;
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Security_Http_FirewallMapInterface     $map        A FirewallMapInterface instance
     * @param Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher An EventDispatcherInterface instance
     */
    public function __construct(Symfony_Component_Security_Http_FirewallMapInterface $map, Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher)
    {
        $this->map = $map;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handles security.
     *
     * @param Symfony_Component_HttpKernel_Event_GetResponseEvent $event An GetResponseEvent instance
     */
    public function onKernelRequest(Symfony_Component_HttpKernel_Event_GetResponseEvent $event)
    {
        if (Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        // register listeners for this firewall
        list($listeners, $exception) = $this->map->getListeners($event->getRequest());
        if (null !== $exception) {
            $exception->register($this->dispatcher);
        }

        // initiate the listener chain
        foreach ($listeners as $listener) {
            $response = $listener->handle($event);

            if ($event->hasResponse()) {
                break;
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(Symfony_Component_HttpKernel_KernelEvents::REQUEST => array('onKernelRequest', 8));
    }
}
