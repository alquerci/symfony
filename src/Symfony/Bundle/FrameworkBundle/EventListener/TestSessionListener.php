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
 * TestSessionListener.
 *
 * Saves session in test environment.
 *
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_EventListener_TestSessionListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    protected $container;

    public function __construct(Symfony_Component_DependencyInjection_ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(Symfony_Component_HttpKernel_Event_GetResponseEvent $event)
    {
        if (Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        // bootstrap the session
        if (!$this->container->has('session')) {
            return;
        }

        $session = $this->container->get('session');
        $cookies = $event->getRequest()->cookies;

        if ($cookies->has($session->getName())) {
            $session->setId($cookies->get($session->getName()));
        }
    }

    /**
     * Checks if session was initialized and saves if current request is master
     * Runs on 'kernel.response' in test environment
     *
     * @param Symfony_Component_HttpKernel_Event_FilterResponseEvent $event
     */
    public function onKernelResponse(Symfony_Component_HttpKernel_Event_FilterResponseEvent $event)
    {
        if (Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $session = $event->getRequest()->getSession();
        if ($session && $session->isStarted()) {
            $session->save();
            $params = session_get_cookie_params();
            if (version_compare(phpversion(), '5.2.0', '>=')) {
                $event->getResponse()->headers->setCookie(new Symfony_Component_HttpFoundation_Cookie($session->getName(), $session->getId(), 0 === $params['lifetime'] ? 0 : time() + $params['lifetime'], $params['path'], $params['domain'], $params['secure'], $params['httponly']));
            } else {
                $event->getResponse()->headers->setCookie(new Symfony_Component_HttpFoundation_Cookie($session->getName(), $session->getId(), 0 === $params['lifetime'] ? 0 : time() + $params['lifetime'], $params['path'], $params['domain'], $params['secure']));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            Symfony_Component_HttpKernel_KernelEvents::REQUEST => array('onKernelRequest', 192),
            Symfony_Component_HttpKernel_KernelEvents::RESPONSE => array('onKernelResponse', -128),
        );
    }
}
