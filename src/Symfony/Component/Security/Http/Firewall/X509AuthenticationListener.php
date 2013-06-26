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
 * X509 authentication listener.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Http_Firewall_X509AuthenticationListener extends Symfony_Component_Security_Http_Firewall_AbstractPreAuthenticatedListener
{
    private $userKey;
    private $credentialKey;

    public function __construct(Symfony_Component_Security_Core_SecurityContextInterface $securityContext, Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface $authenticationManager, $providerKey, $userKey = 'SSL_CLIENT_S_DN_Email', $credentialKey = 'SSL_CLIENT_S_DN', Psr_Log_LoggerInterface $logger = null, Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher = null)
    {
        parent::__construct($securityContext, $authenticationManager, $providerKey, $logger, $dispatcher);

        $this->userKey = $userKey;
        $this->credentialKey = $credentialKey;
    }

    protected function getPreAuthenticatedData(Symfony_Component_HttpFoundation_Request $request)
    {
        if (!$request->server->has($this->userKey)) {
            throw new Symfony_Component_Security_Core_Exception_BadCredentialsException(sprintf('SSL key was not found: %s', $this->userKey));
        }

        return array($request->server->get($this->userKey), $request->server->get($this->credentialKey, ''));
    }
}
