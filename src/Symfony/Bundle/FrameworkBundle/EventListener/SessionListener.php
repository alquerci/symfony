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
 * Sets the session in the request.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Bundle_FrameworkBundle_EventListener_SessionListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    /**
     * @var Symfony_Component_DependencyInjection_ContainerInterface
     */
    private $container;

    public function __construct(Symfony_Component_DependencyInjection_ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(Symfony_Component_HttpKernel_Event_GetResponseEvent $event)
    {
        if (Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->container->has('session') || $request->hasSession()) {
            return;
        }

        $request->setSession($this->container->get('session'));
    }

    public static function getSubscribedEvents()
    {
        return array(
            Symfony_Component_HttpKernel_KernelEvents::REQUEST => array('onKernelRequest', 128),
        );
    }
}
