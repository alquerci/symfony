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
 * ExceptionListener catches authentication exception and converts them to
 * Response instances.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Http_Firewall_ExceptionListener
{
    private $context;
    private $providerKey;
    private $accessDeniedHandler;
    private $authenticationEntryPoint;
    private $authenticationTrustResolver;
    private $errorPage;
    private $logger;
    private $httpUtils;

    public function __construct(Symfony_Component_Security_Core_SecurityContextInterface $context, Symfony_Component_Security_Core_Authentication_AuthenticationTrustResolverInterface $trustResolver, Symfony_Component_Security_Http_HttpUtils $httpUtils, $providerKey, Symfony_Component_Security_Http_EntryPoint_AuthenticationEntryPointInterface $authenticationEntryPoint = null, $errorPage = null, Symfony_Component_Security_Http_Authorization_AccessDeniedHandlerInterface $accessDeniedHandler = null, Psr_Log_LoggerInterface $logger = null)
    {
        $this->context = $context;
        $this->accessDeniedHandler = $accessDeniedHandler;
        $this->httpUtils = $httpUtils;
        $this->providerKey = $providerKey;
        $this->authenticationEntryPoint = $authenticationEntryPoint;
        $this->authenticationTrustResolver = $trustResolver;
        $this->errorPage = $errorPage;
        $this->logger = $logger;
    }

    /**
     * Registers a onKernelException listener to take care of security exceptions.
     *
     * @param Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher An EventDispatcherInterface instance
     */
    public function register(Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener(Symfony_Component_HttpKernel_KernelEvents::EXCEPTION, array($this, 'onKernelException'));
    }

    /**
     * Handles security related exceptions.
     *
     * @param Symfony_Component_HttpKernel_Event_GetResponseForExceptionEvent $event An GetResponseForExceptionEvent instance
     */
    public function onKernelException(Symfony_Component_HttpKernel_Event_GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();

        // determine the actual cause for the exception
        while (is_callable(array($exception, 'getPrevious')) && null !== $previous = $exception->getPrevious()) {
            $exception = $previous;
        }

        if ($exception instanceof Symfony_Component_Security_Core_Exception_AuthenticationException) {
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Authentication exception occurred; redirecting to authentication entry point (%s)', $exception->getMessage()));
            }

            try {
                $response = $this->startAuthentication($request, $exception);
            } catch (Exception $e) {
                $event->setException($e);

                return;
            }
        } elseif ($exception instanceof Symfony_Component_Security_Core_Exception_AccessDeniedException) {
            $event->setException(new Symfony_Component_HttpKernel_Exception_AccessDeniedHttpException($exception->getMessage(), $exception));

            $token = $this->context->getToken();
            if (!$this->authenticationTrustResolver->isFullFledged($token)) {
                if (null !== $this->logger) {
                    $this->logger->debug(sprintf('Access is denied (user is not fully authenticated) by "%s" at line %s; redirecting to authentication entry point', $exception->getFile(), $exception->getLine()));
                }

                try {
                    $insufficientAuthenticationException = new Symfony_Component_Security_Core_Exception_InsufficientAuthenticationException('Full authentication is required to access this resource.', 0, $exception);
                    $insufficientAuthenticationException->setToken($token);
                    $response = $this->startAuthentication($request, $insufficientAuthenticationException);
                } catch (Exception $e) {
                    $event->setException($e);

                    return;
                }
            } else {
                if (null !== $this->logger) {
                    $this->logger->debug(sprintf('Access is denied (and user is neither anonymous, nor remember-me) by "%s" at line %s', $exception->getFile(), $exception->getLine()));
                }

                try {
                    if (null !== $this->accessDeniedHandler) {
                        $response = $this->accessDeniedHandler->handle($request, $exception);

                        if (!$response instanceof Symfony_Component_HttpFoundation_Response) {
                            return;
                        }
                    } elseif (null !== $this->errorPage) {
                        $subRequest = $this->httpUtils->createRequest($request, $this->errorPage);
                        $subRequest->attributes->set(Symfony_Component_Security_Core_SecurityContextInterface::ACCESS_DENIED_ERROR, $exception);

                        $response = $event->getKernel()->handle($subRequest, Symfony_Component_HttpKernel_HttpKernelInterface::SUB_REQUEST, true);
                    } else {
                        return;
                    }
                } catch (Exception $e) {
                    if (null !== $this->logger) {
                        $this->logger->error(sprintf('Exception thrown when handling an exception (%s: %s)', get_class($e), $e->getMessage()));
                    }

                    $event->setException(new Symfony_Component_Security_Core_Exception_RuntimeException('Exception thrown when handling an exception.', 0, $e));

                    return;
                }
            }
        } elseif ($exception instanceof Symfony_Component_Security_Core_Exception_LogoutException) {
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Logout exception occurred; wrapping with AccessDeniedHttpException (%s)', $exception->getMessage()));
            }

            return;
        } else {
            return;
        }

        $event->setResponse($response);
    }

    private function startAuthentication(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_Security_Core_Exception_AuthenticationException $authException)
    {
        if (null === $this->authenticationEntryPoint) {
            throw $authException;
        }

        if (null !== $this->logger) {
            $this->logger->debug('Calling Authentication entry point');
        }

        $this->setTargetPath($request);

        if ($authException instanceof Symfony_Component_Security_Core_Exception_AccountStatusException) {
            // remove the security token to prevent infinite redirect loops
            $this->context->setToken(null);
        }

        return $this->authenticationEntryPoint->start($request, $authException);
    }

    protected function setTargetPath(Symfony_Component_HttpFoundation_Request $request)
    {
        // session isn't required when using http basic authentication mechanism for example
        if ($request->hasSession() && $request->isMethodSafe()) {
            $request->getSession()->set('_security.' . $this->providerKey . '.target_path', $request->getUri());
        }
    }
}
