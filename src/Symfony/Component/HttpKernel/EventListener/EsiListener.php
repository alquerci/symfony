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
 * EsiListener adds a Surrogate-Control HTTP header when the Response needs to be parsed for ESI.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_HttpKernel_EventListener_EsiListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    private $esi;

    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpKernel_HttpCache_Esi $esi An ESI instance
     */
    public function __construct(Symfony_Component_HttpKernel_HttpCache_Esi $esi = null)
    {
        $this->esi = $esi;
    }

    /**
     * Filters the Response.
     *
     * @param Symfony_Component_HttpKernel_Event_FilterResponseEvent $event A FilterResponseEvent instance
     */
    public function onKernelResponse(Symfony_Component_HttpKernel_Event_FilterResponseEvent $event)
    {
        if (Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType() || null === $this->esi) {
            return;
        }

        $this->esi->addSurrogateControl($event->getResponse());
    }

    public static function getSubscribedEvents()
    {
        return array(
            Symfony_Component_HttpKernel_KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }
}
