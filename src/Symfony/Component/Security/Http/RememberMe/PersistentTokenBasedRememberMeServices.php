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
 * Concrete implementation of the RememberMeServicesInterface which needs
 * an implementation of TokenProviderInterface for providing remember-me
 * capabilities.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Security_Http_RememberMe_PersistentTokenBasedRememberMeServices extends Symfony_Component_Security_Http_RememberMe_AbstractRememberMeServices
{
    private $tokenProvider;
    private $secureRandom;

    /**
     * Constructor.
     *
     * @param array                 $userProviders
     * @param string                $key
     * @param string                $providerKey
     * @param array                 $options
     * @param Psr_Log_LoggerInterface       $logger
     * @param Symfony_Component_Security_Core_Util_SecureRandomInterface $secureRandom
     */
    public function __construct(array $userProviders, $key, $providerKey, array $options = array(), Psr_Log_LoggerInterface $logger = null, Symfony_Component_Security_Core_Util_SecureRandomInterface $secureRandom)
    {
        parent::__construct($userProviders, $key, $providerKey, $options, $logger);

        $this->secureRandom = $secureRandom;
    }

    /**
     * Sets the token provider
     *
     * @param Symfony_Component_Security_Core_Authentication_RememberMe_TokenProviderInterface $tokenProvider
     */
    public function setTokenProvider(Symfony_Component_Security_Core_Authentication_RememberMe_TokenProviderInterface $tokenProvider)
    {
        $this->tokenProvider = $tokenProvider;
    }

    /**
     * {@inheritDoc}
     */
    protected function cancelCookie(Symfony_Component_HttpFoundation_Request $request)
    {
        // Delete cookie on the client
        parent::cancelCookie($request);

        // Delete cookie from the tokenProvider
        if (null !== ($cookie = $request->cookies->get($this->options['name']))
            && count($parts = $this->decodeCookie($cookie)) === 2
        ) {
            list($series, $tokenValue) = $parts;
            $this->tokenProvider->deleteTokenBySeries($series);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function processAutoLoginCookie(array $cookieParts, Symfony_Component_HttpFoundation_Request $request)
    {
        if (count($cookieParts) !== 2) {
            throw new Symfony_Component_Security_Core_Exception_AuthenticationException('The cookie is invalid.');
        }

        list($series, $tokenValue) = $cookieParts;
        $persistentToken = $this->tokenProvider->loadTokenBySeries($series);

        if ($persistentToken->getTokenValue() !== $tokenValue) {
            throw new Symfony_Component_Security_Core_Exception_CookieTheftException('This token was already used. The account is possibly compromised.');
        }

        if ($persistentToken->getLastUsed()->getTimestamp() + $this->options['lifetime'] < time()) {
            throw new Symfony_Component_Security_Core_Exception_AuthenticationException('The cookie has expired.');
        }

        $series = $persistentToken->getSeries();
        $tokenValue = base64_encode($this->secureRandom->nextBytes(64));
        $this->tokenProvider->updateToken($series, $tokenValue, new DateTime());
        $request->attributes->set(self::COOKIE_ATTR_NAME,
            new Symfony_Component_HttpFoundation_Cookie(
                $this->options['name'],
                $this->encodeCookie(array($series, $tokenValue)),
                time() + $this->options['lifetime'],
                $this->options['path'],
                $this->options['domain'],
                $this->options['secure'],
                $this->options['httponly']
            )
        );

        return $this->getUserProvider($persistentToken->getClass())->loadUserByUsername($persistentToken->getUsername());
    }

    /**
     * {@inheritDoc}
     */
    protected function onLoginSuccess(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_HttpFoundation_Response $response, Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        $series = base64_encode($this->secureRandom->nextBytes(64));
        $tokenValue = base64_encode($this->secureRandom->nextBytes(64));

        $this->tokenProvider->createNewToken(
            new Symfony_Component_Security_Core_Authentication_RememberMe_PersistentToken(
                get_class($user = $token->getUser()),
                $user->getUsername(),
                $series,
                $tokenValue,
                new DateTime()
            )
        );

        $response->headers->setCookie(
            new Symfony_Component_HttpFoundation_Cookie(
                $this->options['name'],
                $this->encodeCookie(array($series, $tokenValue)),
                time() + $this->options['lifetime'],
                $this->options['path'],
                $this->options['domain'],
                $this->options['secure'],
                $this->options['httponly']
            )
        );
    }
}
