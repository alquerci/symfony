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
 * SwitchUserListener allows a user to impersonate another one temporarily
 * (like the Unix su command).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Http_Firewall_SwitchUserListener implements Symfony_Component_Security_Http_Firewall_ListenerInterface
{
    private $securityContext;
    private $provider;
    private $userChecker;
    private $providerKey;
    private $accessDecisionManager;
    private $usernameParameter;
    private $role;
    private $logger;
    private $dispatcher;

    /**
     * Constructor.
     */
    public function __construct(Symfony_Component_Security_Core_SecurityContextInterface $securityContext, Symfony_Component_Security_Core_User_UserProviderInterface $provider, Symfony_Component_Security_Core_User_UserCheckerInterface $userChecker, $providerKey, Symfony_Component_Security_Core_Authorization_AccessDecisionManagerInterface $accessDecisionManager, Psr_Log_LoggerInterface $logger = null, $usernameParameter = '_switch_user', $role = 'ROLE_ALLOWED_TO_SWITCH', Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher = null)
    {
        if (empty($providerKey)) {
            throw new InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->securityContext = $securityContext;
        $this->provider = $provider;
        $this->userChecker = $userChecker;
        $this->providerKey = $providerKey;
        $this->accessDecisionManager = $accessDecisionManager;
        $this->usernameParameter = $usernameParameter;
        $this->role = $role;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handles digest authentication.
     *
     * @param Symfony_Component_HttpKernel_Event_GetResponseEvent $event A GetResponseEvent instance
     *
     * @throws LogicException
     */
    public function handle(Symfony_Component_HttpKernel_Event_GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->get($this->usernameParameter)) {
            return;
        }

        if ('_exit' === $request->get($this->usernameParameter)) {
            $this->securityContext->setToken($this->attemptExitUser($request));
        } else {
            try {
                $this->securityContext->setToken($this->attemptSwitchUser($request));
            } catch (Symfony_Component_Security_Core_Exception_AuthenticationException $e) {
                throw new LogicException(sprintf('Switch User failed: "%s"', $e->getMessage()));
            }
        }

        $request->server->set('QUERY_STRING', '');
        $response = new Symfony_Component_HttpFoundation_RedirectResponse($request->getUri(), 302);

        $event->setResponse($response);
    }

    /**
     * Attempts to switch to another user.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     *
     * @return Symfony_Component_Security_Core_Authentication_Token_TokenInterface|null The new TokenInterface if successfully switched, null otherwise
     *
     * @throws LogicException
     * @throws Symfony_Component_Security_Core_Exception_AccessDeniedException
     */
    private function attemptSwitchUser(Symfony_Component_HttpFoundation_Request $request)
    {
        $token = $this->securityContext->getToken();
        $originalToken = $this->getOriginalToken($token);

        if (false !== $originalToken) {
            if ($token->getUsername() === $request->get($this->usernameParameter)) {
                return $token;
            } else {
                throw new LogicException(sprintf('You are already switched to "%s" user.', $token->getUsername()));
            }
        }

        if (false === $this->accessDecisionManager->decide($token, array($this->role))) {
            throw new Symfony_Component_Security_Core_Exception_AccessDeniedException();
        }

        $username = $request->get($this->usernameParameter);

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Attempt to switch to user "%s"', $username));
        }

        $user = $this->provider->loadUserByUsername($username);
        $this->userChecker->checkPostAuth($user);

        $roles = $user->getRoles();
        $roles[] = new Symfony_Component_Security_Core_Role_SwitchUserRole('ROLE_PREVIOUS_ADMIN', $this->securityContext->getToken());

        $token = new Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken($user, $user->getPassword(), $this->providerKey, $roles);

        if (null !== $this->dispatcher) {
            $switchEvent = new Symfony_Component_Security_Http_Event_SwitchUserEvent($request, $token->getUser());
            $this->dispatcher->dispatch(Symfony_Component_Security_Http_SecurityEvents::SWITCH_USER, $switchEvent);
        }

        return $token;
    }

    /**
     * Attempts to exit from an already switched user.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     *
     * @return Symfony_Component_Security_Core_Authentication_Token_TokenInterface The original TokenInterface instance
     *
     * @throws Symfony_Component_Security_Core_Exception_AuthenticationCredentialsNotFoundException
     */
    private function attemptExitUser(Symfony_Component_HttpFoundation_Request $request)
    {
        if (false === $original = $this->getOriginalToken($this->securityContext->getToken())) {
            throw new Symfony_Component_Security_Core_Exception_AuthenticationCredentialsNotFoundException('Could not find original Token object.');
        }

        if (null !== $this->dispatcher) {
            $switchEvent = new Symfony_Component_Security_Http_Event_SwitchUserEvent($request, $original->getUser());
            $this->dispatcher->dispatch(Symfony_Component_Security_Http_SecurityEvents::SWITCH_USER, $switchEvent);
        }

        return $original;
    }

    /**
     * Gets the original Token from a switched one.
     *
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token A switched TokenInterface instance
     *
     * @return Symfony_Component_Security_Core_Authentication_Token_TokenInterface|false The original TokenInterface instance, false if the current TokenInterface is not switched
     */
    private function getOriginalToken(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        foreach ($token->getRoles() as $role) {
            if ($role instanceof Symfony_Component_Security_Core_Role_SwitchUserRole) {
                return $role->getSource();
            }
        }

        return false;
    }
}
