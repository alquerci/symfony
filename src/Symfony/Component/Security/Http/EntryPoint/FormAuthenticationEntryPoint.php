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
 * FormAuthenticationEntryPoint starts an authentication via a login form.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Http_EntryPoint_FormAuthenticationEntryPoint implements Symfony_Component_Security_Http_EntryPoint_AuthenticationEntryPointInterface
{
    private $loginPath;
    private $useForward;
    private $httpKernel;
    private $httpUtils;

    /**
     * Constructor
     *
     * @param Symfony_Component_HttpKernel_HttpKernelInterface $kernel
     * @param Symfony_Component_Security_Http_HttpUtils           $httpUtils  An HttpUtils instance
     * @param string              $loginPath  The path to the login form
     * @param Boolean             $useForward Whether to forward or redirect to the login form
     */
    public function __construct(Symfony_Component_HttpKernel_HttpKernelInterface $kernel, Symfony_Component_Security_Http_HttpUtils $httpUtils, $loginPath, $useForward = false)
    {
        $this->httpKernel = $kernel;
        $this->httpUtils = $httpUtils;
        $this->loginPath = $loginPath;
        $this->useForward = (Boolean) $useForward;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_Security_Core_Exception_AuthenticationException $authException = null)
    {
        if ($this->useForward) {
            $subRequest = $this->httpUtils->createRequest($request, $this->loginPath);

            return $this->httpKernel->handle($subRequest, Symfony_Component_HttpKernel_HttpKernelInterface::SUB_REQUEST);
        }

        return $this->httpUtils->createRedirectResponse($request, $this->loginPath);
    }
}
