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
 * RememberMeListener implements authentication capabilities via a cookie
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Security_Http_Firewall_RememberMeListener implements Symfony_Component_Security_Http_Firewall_ListenerInterface
{
    private $securityContext;
    private $rememberMeServices;
    private $authenticationManager;
    private $logger;
    private $dispatcher;

    /**
     * Constructor
     *
     * @param Symfony_Component_Security_Core_SecurityContextInterface       $securityContext
     * @param Symfony_Component_Security_Http_RememberMe_RememberMeServicesInterface    $rememberMeServices
     * @param Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface $authenticationManager
     * @param Psr_Log_LoggerInterface                $logger
     * @param Symfony_Component_EventDispatcher_EventDispatcherInterface       $dispatcher
     */
    public function __construct(Symfony_Component_Security_Core_SecurityContextInterface $securityContext, Symfony_Component_Security_Http_RememberMe_RememberMeServicesInterface $rememberMeServices, Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface $authenticationManager, Psr_Log_LoggerInterface $logger = null, Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher = null)
    {
        $this->securityContext = $securityContext;
        $this->rememberMeServices = $rememberMeServices;
        $this->authenticationManager = $authenticationManager;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handles remember-me cookie based authentication.
     *
     * @param Symfony_Component_HttpKernel_Event_GetResponseEvent $event A GetResponseEvent instance
     */
    public function handle(Symfony_Component_HttpKernel_Event_GetResponseEvent $event)
    {
        if (null !== $this->securityContext->getToken()) {
            return;
        }

        $request = $event->getRequest();
        if (null === $token = $this->rememberMeServices->autoLogin($request)) {
            return;
        }

        try {
            $token = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($token);

            if (null !== $this->dispatcher) {
                $loginEvent = new Symfony_Component_Security_Http_Event_InteractiveLoginEvent($request, $token);
                $this->dispatcher->dispatch(Symfony_Component_Security_Http_SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);
            }

            if (null !== $this->logger) {
                $this->logger->debug('SecurityContext populated with remember-me token.');
            }
        } catch (Symfony_Component_Security_Core_Exception_AuthenticationException $failed) {
            if (null !== $this->logger) {
                $this->logger->warning(
                    'SecurityContext not populated with remember-me token as the'
                   .' AuthenticationManager rejected the AuthenticationToken returned'
                   .' by the RememberMeServices: '.$failed->getMessage()
                );
            }

            $this->rememberMeServices->loginFail($request);
        }
    }
}
