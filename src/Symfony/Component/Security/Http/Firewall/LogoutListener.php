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
 * LogoutListener logout users.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Http_Firewall_LogoutListener implements Symfony_Component_Security_Http_Firewall_ListenerInterface
{
    private $securityContext;
    private $options;
    private $handlers;
    private $successHandler;
    private $httpUtils;
    private $csrfProvider;

    /**
     * Constructor
     *
     * @param Symfony_Component_Security_Core_SecurityContextInterface      $securityContext
     * @param Symfony_Component_Security_Http_HttpUtils                     $httpUtils       An HttpUtilsInterface instance
     * @param Symfony_Component_Security_Http_Logout_LogoutSuccessHandlerInterface $successHandler  A LogoutSuccessHandlerInterface instance
     * @param array                         $options         An array of options to process a logout attempt
     * @param Symfony_Component_Form_Extension_Csrf_CsrfProvider_CsrfProviderInterface         $csrfProvider    A CsrfProviderInterface instance
     */
    public function __construct(Symfony_Component_Security_Core_SecurityContextInterface $securityContext, Symfony_Component_Security_Http_HttpUtils $httpUtils, Symfony_Component_Security_Http_Logout_LogoutSuccessHandlerInterface $successHandler, array $options = array(), Symfony_Component_Form_Extension_Csrf_CsrfProvider_CsrfProviderInterface $csrfProvider = null)
    {
        $this->securityContext = $securityContext;
        $this->httpUtils = $httpUtils;
        $this->options = array_merge(array(
            'csrf_parameter' => '_csrf_token',
            'intention'      => 'logout',
            'logout_path'    => '/logout',
        ), $options);
        $this->successHandler = $successHandler;
        $this->csrfProvider = $csrfProvider;
        $this->handlers = array();
    }

    /**
     * Adds a logout handler
     *
     * @param Symfony_Component_Security_Http_Logout_LogoutHandlerInterface $handler
     */
    public function addHandler(Symfony_Component_Security_Http_Logout_LogoutHandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * Performs the logout if requested
     *
     * If a CsrfProviderInterface instance is available, it will be used to
     * validate the request.
     *
     * @param Symfony_Component_HttpKernel_Event_GetResponseEvent $event A GetResponseEvent instance
     *
     * @throws Symfony_Component_Security_Core_Exception_InvalidCsrfTokenException if the CSRF token is invalid
     * @throws RuntimeException if the LogoutSuccessHandlerInterface instance does not return a response
     * @throws Symfony_Component_Security_Core_Exception_LogoutException
     */
    public function handle(Symfony_Component_HttpKernel_Event_GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$this->requiresLogout($request)) {
            return;
        }

        if (null !== $this->csrfProvider) {
            $csrfToken = $request->get($this->options['csrf_parameter'], null, true);

            if (false === $this->csrfProvider->isCsrfTokenValid($this->options['intention'], $csrfToken)) {
                throw new Symfony_Component_Security_Core_Exception_LogoutException('Invalid CSRF token.');
            }
        }

        $response = $this->successHandler->onLogoutSuccess($request);
        if (!$response instanceof Symfony_Component_HttpFoundation_Response) {
            throw new RuntimeException('Logout Success Handler did not return a Response.');
        }

        // handle multiple logout attempts gracefully
        if ($token = $this->securityContext->getToken()) {
            foreach ($this->handlers as $handler) {
                $handler->logout($request, $response, $token);
            }
        }

        $this->securityContext->setToken(null);

        $event->setResponse($response);
    }

    /**
     * Whether this request is asking for logout.
     *
     * The default implementation only processed requests to a specific path,
     * but a subclass could change this to logout requests where
     * certain parameters is present.
     *
     * @param Symfony_Component_HttpFoundation_Request $request
     *
     * @return Boolean
     */
    protected function requiresLogout(Symfony_Component_HttpFoundation_Request $request)
    {
        return $this->httpUtils->checkRequestPath($request, $this->options['logout_path']);
    }
}
