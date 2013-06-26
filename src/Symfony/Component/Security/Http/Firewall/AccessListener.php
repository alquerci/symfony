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
 * AccessListener enforces access control rules.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Http_Firewall_AccessListener implements Symfony_Component_Security_Http_Firewall_ListenerInterface
{
    private $context;
    private $accessDecisionManager;
    private $map;
    private $authManager;
    private $logger;

    public function __construct(Symfony_Component_Security_Core_SecurityContextInterface $context, Symfony_Component_Security_Core_Authorization_AccessDecisionManagerInterface $accessDecisionManager, Symfony_Component_Security_Http_AccessMapInterface $map, Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface $authManager, Psr_Log_LoggerInterface $logger = null)
    {
        $this->context = $context;
        $this->accessDecisionManager = $accessDecisionManager;
        $this->map = $map;
        $this->authManager = $authManager;
        $this->logger = $logger;
    }

    /**
     * Handles access authorization.
     *
     * @param Symfony_Component_HttpKernel_Event_GetResponseEvent $event A GetResponseEvent instance
     *
     * @throws Symfony_Component_Security_Core_Exception_AccessDeniedException
     * @throws Symfony_Component_Security_Core_Exception_AuthenticationCredentialsNotFoundException
     */
    public function handle(Symfony_Component_HttpKernel_Event_GetResponseEvent $event)
    {
        if (null === $token = $this->context->getToken()) {
            throw new Symfony_Component_Security_Core_Exception_AuthenticationCredentialsNotFoundException('A Token was not found in the SecurityContext.');
        }

        $request = $event->getRequest();

        list($attributes, $channel) = $this->map->getPatterns($request);

        if (null === $attributes) {
            return;
        }

        if (!$token->isAuthenticated()) {
            $token = $this->authManager->authenticate($token);
            $this->context->setToken($token);
        }

        if (!$this->accessDecisionManager->decide($token, $attributes, $request)) {
            throw new Symfony_Component_Security_Core_Exception_AccessDeniedException();
        }
    }
}
