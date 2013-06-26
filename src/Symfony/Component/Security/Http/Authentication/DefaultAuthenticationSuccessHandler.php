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
 * Class with the default authentication success handling logic.
 *
 * Can be optionally be extended from by the developer to alter the behaviour
 * while keeping the default behaviour.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class Symfony_Component_Security_Http_Authentication_DefaultAuthenticationSuccessHandler implements Symfony_Component_Security_Http_Authentication_AuthenticationSuccessHandlerInterface
{
    protected $httpUtils;
    protected $options;
    protected $providerKey;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Security_Http_HttpUtils $httpUtils
     * @param array     $options   Options for processing a successful authentication attempt.
     */
    public function __construct(Symfony_Component_Security_Http_HttpUtils $httpUtils, array $options)
    {
        $this->httpUtils   = $httpUtils;

        $this->options = array_merge(array(
            'always_use_default_target_path' => false,
            'default_target_path'            => '/',
            'login_path'                     => '/login',
            'target_path_parameter'          => '_target_path',
            'use_referer'                    => false,
        ), $options);
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        return $this->httpUtils->createRedirectResponse($request, $this->determineTargetUrl($request));
    }

    /**
     * Get the provider key.
     *
     * @return string
     */
    public function getProviderKey()
    {
        return $this->providerKey;
    }

    /**
     * Set the provider key.
     *
     * @param string $providerKey
     */
    public function setProviderKey($providerKey)
    {
        $this->providerKey = $providerKey;
    }

    /**
     * Builds the target URL according to the defined options.
     *
     * @param Symfony_Component_HttpFoundation_Request $request
     *
     * @return string
     */
    protected function determineTargetUrl(Symfony_Component_HttpFoundation_Request $request)
    {
        if ($this->options['always_use_default_target_path']) {
            return $this->options['default_target_path'];
        }

        if ($targetUrl = $request->get($this->options['target_path_parameter'], null, true)) {
            return $targetUrl;
        }

        if (null !== $this->providerKey && $targetUrl = $request->getSession()->get('_security.'.$this->providerKey.'.target_path')) {
            $request->getSession()->remove('_security.'.$this->providerKey.'.target_path');

            return $targetUrl;
        }

        if ($this->options['use_referer'] && ($targetUrl = $request->headers->get('Referer')) && $targetUrl !== $this->httpUtils->generateUri($request, $this->options['login_path'])) {
            return $targetUrl;
        }

        return $this->options['default_target_path'];
    }
}
