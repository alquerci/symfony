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
 * Class with the default authentication failure handling logic.
 *
 * Can be optionally be extended from by the developer to alter the behaviour
 * while keeping the default behaviour.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class Symfony_Component_Security_Http_Authentication_DefaultAuthenticationFailureHandler implements Symfony_Component_Security_Http_Authentication_AuthenticationFailureHandlerInterface
{
    protected $httpKernel;
    protected $httpUtils;
    protected $logger;
    protected $options;

    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpKernel_HttpKernelInterface $httpKernel
     * @param Symfony_Component_Security_Http_HttpUtils           $httpUtils
     * @param array               $options    Options for processing a failed authentication attempt.
     * @param Psr_Log_LoggerInterface     $logger     Optional logger
     */
    public function __construct(Symfony_Component_HttpKernel_HttpKernelInterface $httpKernel, Symfony_Component_Security_Http_HttpUtils $httpUtils, array $options, Psr_Log_LoggerInterface $logger = null)
    {
        $this->httpKernel = $httpKernel;
        $this->httpUtils  = $httpUtils;
        $this->logger     = $logger;

        $this->options = array_merge(array(
            'failure_path'           => null,
            'failure_forward'        => false,
            'login_path'             => '/login',
            'failure_path_parameter' => '_failure_path'
        ), $options);
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_Security_Core_Exception_AuthenticationException $exception)
    {
        if ($failureUrl = $request->get($this->options['failure_path_parameter'], null, true)) {
             $this->options['failure_path'] = $failureUrl;
         }

        if (null === $this->options['failure_path']) {
            $this->options['failure_path'] = $this->options['login_path'];
        }

        if ($this->options['failure_forward']) {
            if (null !== $this->logger) {
                $this->logger->debug(sprintf('Forwarding to %s', $this->options['failure_path']));
            }

            $subRequest = $this->httpUtils->createRequest($request, $this->options['failure_path']);
            $subRequest->attributes->set(Symfony_Component_Security_Core_SecurityContextInterface::AUTHENTICATION_ERROR, $exception);

            return $this->httpKernel->handle($subRequest, Symfony_Component_HttpKernel_HttpKernelInterface::SUB_REQUEST);
        }

        if (null !== $this->logger) {
            $this->logger->debug(sprintf('Redirecting to %s', $this->options['failure_path']));
        }

        $request->getSession()->set(Symfony_Component_Security_Core_SecurityContextInterface::AUTHENTICATION_ERROR, $exception);

        return $this->httpUtils->createRedirectResponse($request, $this->options['failure_path']);
    }
}
