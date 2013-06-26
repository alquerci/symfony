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
 * The AbstractAuthenticationListener is the preferred base class for all
 * browser-/HTTP-based authentication requests.
 *
 * Subclasses likely have to implement the following:
 * - an TokenInterface to hold authentication related data
 * - an AuthenticationProvider to perform the actual authentication of the
 *   token, retrieve the UserInterface implementation from a database, and
 *   perform the specific account checks using the UserChecker
 *
 * By default, this listener only is active for a specific path, e.g.
 * /login_check. If you want to change this behavior, you can overwrite the
 * requiresAuthentication() method.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
abstract class Symfony_Component_Security_Http_Firewall_AbstractAuthenticationListener implements Symfony_Component_Security_Http_Firewall_ListenerInterface
{
    protected $options;
    protected $logger;
    protected $authenticationManager;
    protected $providerKey;
    protected $httpUtils;

    private $securityContext;
    private $sessionStrategy;
    private $dispatcher;
    private $successHandler;
    private $failureHandler;
    private $rememberMeServices;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Security_Core_SecurityContextInterface               $securityContext       A SecurityContext instance
     * @param Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface         $authenticationManager An AuthenticationManagerInterface instance
     * @param Symfony_Component_Security_Http_Session_SessionAuthenticationStrategyInterface $sessionStrategy
     * @param Symfony_Component_Security_Http_HttpUtils                              $httpUtils             An HttpUtilsInterface instance
     * @param string                                 $providerKey
     * @param Symfony_Component_Security_Http_Authentication_AuthenticationSuccessHandlerInterface  $successHandler
     * @param Symfony_Component_Security_Http_Authentication_AuthenticationFailureHandlerInterface  $failureHandler
     * @param array                                  $options               An array of options for the processing of a
     *                                                                      successful, or failed authentication attempt
     * @param Psr_Log_LoggerInterface                        $logger                A LoggerInterface instance
     * @param Symfony_Component_EventDispatcher_EventDispatcherInterface               $dispatcher            An EventDispatcherInterface instance
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Symfony_Component_Security_Core_SecurityContextInterface $securityContext, Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface $authenticationManager, Symfony_Component_Security_Http_Session_SessionAuthenticationStrategyInterface $sessionStrategy, Symfony_Component_Security_Http_HttpUtils $httpUtils, $providerKey, Symfony_Component_Security_Http_Authentication_AuthenticationSuccessHandlerInterface $successHandler, Symfony_Component_Security_Http_Authentication_AuthenticationFailureHandlerInterface $failureHandler, array $options = array(), Psr_Log_LoggerInterface $logger = null, Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher = null)
    {
        if (empty($providerKey)) {
            throw new InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->sessionStrategy = $sessionStrategy;
        $this->providerKey = $providerKey;
        $this->successHandler = $successHandler;
        $this->failureHandler = $failureHandler;
        $this->options = array_merge(array(
            'check_path'                     => '/login_check',
        ), $options);
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->httpUtils = $httpUtils;
    }

    /**
     * Sets the RememberMeServices implementation to use
     *
     * @param Symfony_Component_Security_Http_RememberMe_RememberMeServicesInterface $rememberMeServices
     */
    public function setRememberMeServices(Symfony_Component_Security_Http_RememberMe_RememberMeServicesInterface $rememberMeServices)
    {
        $this->rememberMeServices = $rememberMeServices;
    }

    /**
     * Handles form based authentication.
     *
     * @param Symfony_Component_HttpKernel_Event_GetResponseEvent $event A GetResponseEvent instance
     *
     * @throws RuntimeException
     * @throws Symfony_Component_Security_Core_Exception_SessionUnavailableException
     */
    final public function handle(Symfony_Component_HttpKernel_Event_GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$this->requiresAuthentication($request)) {
            return;
        }

        if (!$request->hasSession()) {
            throw new RuntimeException('This authentication method requires a session.');
        }

        try {
            if (!$request->hasPreviousSession()) {
                throw new Symfony_Component_Security_Core_Exception_SessionUnavailableException('Your session has timed out, or you have disabled cookies.');
            }

            if (null === $returnValue = $this->attemptAuthentication($request)) {
                return;
            }

            if ($returnValue instanceof Symfony_Component_Security_Core_Authentication_Token_TokenInterface) {
                $this->sessionStrategy->onAuthentication($request, $returnValue);

                $response = $this->onSuccess($event, $request, $returnValue);
            } elseif ($returnValue instanceof Symfony_Component_HttpFoundation_Response) {
                $response = $returnValue;
            } else {
                throw new RuntimeException('attemptAuthentication() must either return a Response, an implementation of TokenInterface, or null.');
            }
        } catch (Symfony_Component_Security_Core_Exception_AuthenticationException $e) {
            $response = $this->onFailure($event, $request, $e);
        }

        $event->setResponse($response);
    }

    /**
     * Whether this request requires authentication.
     *
     * The default implementation only processed requests to a specific path,
     * but a subclass could change this to only authenticate requests where a
     * certain parameters is present.
     *
     * @param Symfony_Component_HttpFoundation_Request $request
     *
     * @return Boolean
     */
    protected function requiresAuthentication(Symfony_Component_HttpFoundation_Request $request)
    {
        return $this->httpUtils->checkRequestPath($request, $this->options['check_path']);
    }

    /**
     * Performs authentication.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     *
     * @return Symfony_Component_Security_Core_Authentication_Token_TokenInterface|Symfony_Component_HttpFoundation_Response|null The authenticated token, null if full authentication is not possible, or a Response
     *
     * @throws Symfony_Component_Security_Core_Exception_AuthenticationException if the authentication fails
     */
    abstract protected function attemptAuthentication(Symfony_Component_HttpFoundation_Request $request);

    private function onFailure(Symfony_Component_HttpKernel_Event_GetResponseEvent $event, Symfony_Component_HttpFoundation_Request $request, Symfony_Component_Security_Core_Exception_AuthenticationException $failed)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Authentication request failed: %s', $failed->getMessage()));
        }

        $this->securityContext->setToken(null);

        $response = $this->failureHandler->onAuthenticationFailure($request, $failed);

        if (!$response instanceof Symfony_Component_HttpFoundation_Response) {
            throw new RuntimeException('Authentication Failure Handler did not return a Response.');
        }

        return $response;
    }

    private function onSuccess(Symfony_Component_HttpKernel_Event_GetResponseEvent $event, Symfony_Component_HttpFoundation_Request $request, Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('User "%s" has been authenticated successfully', $token->getUsername()));
        }

        $this->securityContext->setToken($token);

        $session = $request->getSession();
        $session->remove(Symfony_Component_Security_Core_SecurityContextInterface::AUTHENTICATION_ERROR);
        $session->remove(Symfony_Component_Security_Core_SecurityContextInterface::LAST_USERNAME);

        if (null !== $this->dispatcher) {
            $loginEvent = new Symfony_Component_Security_Http_Event_InteractiveLoginEvent($request, $token);
            $this->dispatcher->dispatch(Symfony_Component_Security_Http_SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);
        }

        $response = $this->successHandler->onAuthenticationSuccess($request, $token);

        if (!$response instanceof Symfony_Component_HttpFoundation_Response) {
            throw new RuntimeException('Authentication Success Handler did not return a Response.');
        }

        if (null !== $this->rememberMeServices) {
            $this->rememberMeServices->loginSuccess($request, $response, $token);
        }

        return $response;
    }
}
