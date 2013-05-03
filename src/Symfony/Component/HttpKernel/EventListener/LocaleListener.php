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
 * Initializes the locale based on the current request.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_HttpKernel_EventListener_LocaleListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    private $router;
    private $defaultLocale;
    private $locales = array();

    public function __construct($defaultLocale = 'en', Symfony_Component_Routing_RequestContextAwareInterface $router = null)
    {
        $this->defaultLocale = $defaultLocale;
        $this->router = $router;
    }

    public function onKernelResponse(Symfony_Component_HttpKernel_Event_FilterResponseEvent $event)
    {
        array_shift($this->locales);

        // setting back the locale to the previous value
        $locale = isset($this->locales[0]) ? $this->locales[0] : $this->defaultLocale;
        $request = $event->getRequest();
        $this->setLocale($request, $locale);
    }

    public function onKernelRequest(Symfony_Component_HttpKernel_Event_GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $request->setDefaultLocale($this->defaultLocale);
        $this->setLocale($request, $request->attributes->get('_locale', $this->defaultLocale));

        array_unshift($this->locales, $request->getLocale());
    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after the Router to have access to the _locale
            Symfony_Component_HttpKernel_KernelEvents::REQUEST => array(array('onKernelRequest', 16)),
            Symfony_Component_HttpKernel_KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }

    private function setLocale(Symfony_Component_HttpFoundation_Request $request, $locale)
    {
        $request->setLocale($locale);

        if (null !== $this->router) {
            $this->router->getContext()->setParameter('_locale', $request->getLocale());
        }
    }
}
