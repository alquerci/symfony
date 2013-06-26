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
 * ContextListener manages the SecurityContext persistence through a session.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Security_Http_Firewall_ContextListener implements Symfony_Component_Security_Http_Firewall_ListenerInterface
{
    private $context;
    private $contextKey;
    private $logger;
    private $userProviders;
    private $dispatcher;

    public function __construct(Symfony_Component_Security_Core_SecurityContextInterface $context, array $userProviders, $contextKey, Psr_Log_LoggerInterface $logger = null, Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher = null)
    {
        if (empty($contextKey)) {
            throw new InvalidArgumentException('$contextKey must not be empty.');
        }

        foreach ($userProviders as $userProvider) {
            if (!$userProvider instanceof Symfony_Component_Security_Core_User_UserProviderInterface) {
                throw new InvalidArgumentException(sprintf('User provider "%s" must implement "Symfony_Component_Security_Core_User_UserProviderInterface".', get_class($userProvider)));
            }
        }

        $this->context = $context;
        $this->userProviders = $userProviders;
        $this->contextKey = $contextKey;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Reads the SecurityContext from the session.
     *
     * @param Symfony_Component_HttpKernel_Event_GetResponseEvent $event A GetResponseEvent instance
     */
    public function handle(Symfony_Component_HttpKernel_Event_GetResponseEvent $event)
    {
        if (null !== $this->dispatcher && Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $this->dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, array($this, 'onKernelResponse'));
        }

        $request = $event->getRequest();
        $session = $request->hasPreviousSession() ? $request->getSession() : null;

        if (null === $session || null === $token = $session->get('_security_'.$this->contextKey)) {
            $this->context->setToken(null);

            return;
        }

        $token = unserialize($token);

        if (null !== $this->logger) {
            $this->logger->debug('Read SecurityContext from the session');
        }

        if ($token instanceof Symfony_Component_Security_Core_Authentication_Token_TokenInterface) {
            $token = $this->refreshUser($token);
        } elseif (null !== $token) {
            if (null !== $this->logger) {
                $this->logger->warning(sprintf('Session includes a "%s" where a security token is expected', is_object($token) ? get_class($token) : gettype($token)));
            }

            $token = null;
        }

        $this->context->setToken($token);
    }

    /**
     * Writes the SecurityContext to the session.
     *
     * @param Symfony_Component_HttpKernel_Event_FilterResponseEvent $event A FilterResponseEvent instance
     */
    public function onKernelResponse(Symfony_Component_HttpKernel_Event_FilterResponseEvent $event)
    {
        if (Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        if (!$event->getRequest()->hasSession()) {
            return;
        }

        if (null !== $this->logger) {
            $this->logger->debug('Write SecurityContext in the session');
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        if (null === $session) {
            return;
        }

        if ((null === $token = $this->context->getToken()) || ($token instanceof Symfony_Component_Security_Core_Authentication_Token_AnonymousToken)) {
            if ($request->hasPreviousSession()) {
                $session->remove('_security_'.$this->contextKey);
            }
        } else {
            $session->set('_security_'.$this->contextKey, serialize($token));
        }
    }

    /**
     * Refreshes the user by reloading it from the user provider
     *
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token
     *
     * @return Symfony_Component_Security_Core_Authentication_Token_TokenInterface|null
     *
     * @throws RuntimeException
     */
    private function refreshUser(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof Symfony_Component_Security_Core_User_UserInterface) {
            return $token;
        }

        if (null !== $this->logger) {
            $this->logger->debug(sprintf('Reloading user from user provider.'));
        }

        foreach ($this->userProviders as $provider) {
            try {
                $token->setUser($provider->refreshUser($user));

                if (null !== $this->logger) {
                    $this->logger->debug(sprintf('Username "%s" was reloaded from user provider.', $user->getUsername()));
                }

                return $token;
            } catch (Symfony_Component_Security_Core_Exception_UnsupportedUserException $unsupported) {
                // let's try the next user provider
            } catch (Symfony_Component_Security_Core_Exception_UsernameNotFoundException $notFound) {
                if (null !== $this->logger) {
                    $this->logger->warning(sprintf('Username "%s" could not be found.', $user->getUsername()));
                }

                return null;
            }
        }

        throw new RuntimeException(sprintf('There is no user provider for user "%s".', get_class($user)));
    }
}
