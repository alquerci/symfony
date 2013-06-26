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
 * UsernamePasswordFormAuthenticationListener is the default implementation of
 * an authentication via a simple form composed of a username and a password.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Http_Firewall_UsernamePasswordFormAuthenticationListener extends Symfony_Component_Security_Http_Firewall_AbstractAuthenticationListener
{
    private $csrfProvider;

    /**
     * {@inheritdoc}
     */
    public function __construct(Symfony_Component_Security_Core_SecurityContextInterface $securityContext, Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface $authenticationManager, Symfony_Component_Security_Http_Session_SessionAuthenticationStrategyInterface $sessionStrategy, Symfony_Component_Security_Http_HttpUtils $httpUtils, $providerKey, Symfony_Component_Security_Http_Authentication_AuthenticationSuccessHandlerInterface $successHandler, Symfony_Component_Security_Http_Authentication_AuthenticationFailureHandlerInterface $failureHandler, array $options = array(), Psr_Log_LoggerInterface $logger = null, Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher = null, Symfony_Component_Form_Extension_Csrf_CsrfProvider_CsrfProviderInterface $csrfProvider = null)
    {
        parent::__construct($securityContext, $authenticationManager, $sessionStrategy, $httpUtils, $providerKey, $successHandler, $failureHandler, array_merge(array(
            'username_parameter' => '_username',
            'password_parameter' => '_password',
            'csrf_parameter'     => '_csrf_token',
            'intention'          => 'authenticate',
            'post_only'          => true,
        ), $options), $logger, $dispatcher);

        $this->csrfProvider = $csrfProvider;
    }

    /**
     * {@inheritdoc}
     */
    protected function requiresAuthentication(Symfony_Component_HttpFoundation_Request $request)
    {
        if ($this->options['post_only'] && !$request->isMethod('POST')) {
            return false;
        }

        return parent::requiresAuthentication($request);
    }

    /**
     * {@inheritdoc}
     */
    protected function attemptAuthentication(Symfony_Component_HttpFoundation_Request $request)
    {
        if (null !== $this->csrfProvider) {
            $csrfToken = $request->get($this->options['csrf_parameter'], null, true);

            if (false === $this->csrfProvider->isCsrfTokenValid($this->options['intention'], $csrfToken)) {
                throw new Symfony_Component_Security_Core_Exception_InvalidCsrfTokenException('Invalid CSRF token.');
            }
        }

        if ($this->options['post_only']) {
            $username = trim($request->request->get($this->options['username_parameter'], null, true));
            $password = $request->request->get($this->options['password_parameter'], null, true);
        } else {
            $username = trim($request->get($this->options['username_parameter'], null, true));
            $password = $request->get($this->options['password_parameter'], null, true);
        }

        $request->getSession()->set(Symfony_Component_Security_Core_SecurityContextInterface::LAST_USERNAME, $username);

        return $this->authenticationManager->authenticate(new Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken($username, $password, $this->providerKey));
    }
}
