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
 * DigestAuthenticationEntryPoint starts an HTTP Digest authentication.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Http_EntryPoint_DigestAuthenticationEntryPoint implements Symfony_Component_Security_Http_EntryPoint_AuthenticationEntryPointInterface
{
    private $key;
    private $realmName;
    private $nonceValiditySeconds;
    private $logger;

    public function __construct($realmName, $key, $nonceValiditySeconds = 300, Psr_Log_LoggerInterface $logger = null)
    {
        $this->realmName = $realmName;
        $this->key = $key;
        $this->nonceValiditySeconds = $nonceValiditySeconds;
        $this->logger = $logger;
    }

    public function start(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_Security_Core_Exception_AuthenticationException $authException = null)
    {
        $expiryTime = microtime(true) + $this->nonceValiditySeconds * 1000;
        $signatureValue = md5($expiryTime.':'.$this->key);
        $nonceValue = $expiryTime.':'.$signatureValue;
        $nonceValueBase64 = base64_encode($nonceValue);

        $authenticateHeader = sprintf('Digest realm="%s", qop="auth", nonce="%s"', $this->realmName, $nonceValueBase64);

        if ($authException instanceof Symfony_Component_Security_Core_Exception_NonceExpiredException) {
            $authenticateHeader = $authenticateHeader.', stale="true"';
        }

        if (null !== $this->logger) {
            $this->logger->debug(sprintf('WWW-Authenticate header sent to user agent: "%s"', $authenticateHeader));
        }

        $response = new Symfony_Component_HttpFoundation_Response();
        $response->headers->set('WWW-Authenticate', $authenticateHeader);
        $response->setStatusCode(401);

        return $response;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getRealmName()
    {
        return $this->realmName;
    }
}
