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
 * AbstractPreAuthenticatedListener is the base class for all listener that
 * authenticates users based on a pre-authenticated request (like a certificate
 * for instance).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Symfony_Component_Security_Http_Firewall_AbstractPreAuthenticatedListener implements Symfony_Component_Security_Http_Firewall_ListenerInterface
{
    protected $logger;
    private $securityContext;
    private $authenticationManager;
    private $providerKey;
    private $dispatcher;

    public function __construct(Symfony_Component_Security_Core_SecurityContextInterface $securityContext, Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface $authenticationManager, $providerKey, Psr_Log_LoggerInterface $logger = null, Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher = null)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->providerKey = $providerKey;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handles X509 authentication.
     *
     * @param Symfony_Component_HttpKernel_Event_GetResponseEvent $event A GetResponseEvent instance
     */
    final public function handle(Symfony_Component_HttpKernel_Event_GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (null !== $this->logger) {
            $this->logger->debug(sprintf('Checking secure context token: %s', $this->securityContext->getToken()));
        }

        list($user, $credentials) = $this->getPreAuthenticatedData($request);

        if (null !== $token = $this->securityContext->getToken()) {
            if ($token instanceof Symfony_Component_Security_Core_Authentication_Token_PreAuthenticatedToken && $token->isAuthenticated() && $token->getUsername() === $user) {
                return;
            }
        }

        if (null !== $this->logger) {
            $this->logger->debug(sprintf('Trying to pre-authenticate user "%s"', $user));
        }

        try {
            $token = $this->authenticationManager->authenticate(new Symfony_Component_Security_Core_Authentication_Token_PreAuthenticatedToken($user, $credentials, $this->providerKey));

            if (null !== $this->logger) {
                $this->logger->info(sprintf('Authentication success: %s', $token));
            }
            $this->securityContext->setToken($token);

            if (null !== $this->dispatcher) {
                $loginEvent = new Symfony_Component_Security_Http_Event_InteractiveLoginEvent($request, $token);
                $this->dispatcher->dispatch(Symfony_Component_Security_Http_SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);
            }
        } catch (Symfony_Component_Security_Core_Exception_AuthenticationException $failed) {
            $this->securityContext->setToken(null);

            if (null !== $this->logger) {
                $this->logger->info(sprintf("Cleared security context due to exception: %s", $failed->getMessage()));
            }
        }
    }

    /**
     * Gets the user and credentials from the Request.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     *
     * @return array An array composed of the user and the credentials
     */
    abstract protected function getPreAuthenticatedData(Symfony_Component_HttpFoundation_Request $request);
}
