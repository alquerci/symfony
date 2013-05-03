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
 * StreamedResponseListener is responsible for sending the Response
 * to the client.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_HttpKernel_EventListener_StreamedResponseListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    /**
     * Filters the Response.
     *
     * @param Symfony_Component_HttpKernel_Event_FilterResponseEvent $event A FilterResponseEvent instance
     */
    public function onKernelResponse(Symfony_Component_HttpKernel_Event_FilterResponseEvent $event)
    {
        if (Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $response = $event->getResponse();

        if ($response instanceof Symfony_Component_HttpFoundation_StreamedResponse) {
            $response->send();
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            Symfony_Component_HttpKernel_KernelEvents::RESPONSE => array('onKernelResponse', -1024),
        );
    }
}
