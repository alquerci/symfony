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
 * Adds remember-me cookies to the Response.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Security_Http_RememberMe_ResponseListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    public function onKernelResponse(Symfony_Component_HttpKernel_Event_FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if ($request->attributes->has(Symfony_Component_Security_Http_RememberMe_RememberMeServicesInterface::COOKIE_ATTR_NAME)) {
            $response->headers->setCookie($request->attributes->get(Symfony_Component_Security_Http_RememberMe_RememberMeServicesInterface::COOKIE_ATTR_NAME));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(Symfony_Component_HttpKernel_KernelEvents::RESPONSE => 'onKernelResponse');
    }
}
