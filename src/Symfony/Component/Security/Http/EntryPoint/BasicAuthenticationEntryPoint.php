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
 * BasicAuthenticationEntryPoint starts an HTTP Basic authentication.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Http_EntryPoint_BasicAuthenticationEntryPoint implements Symfony_Component_Security_Http_EntryPoint_AuthenticationEntryPointInterface
{
    private $realmName;

    public function __construct($realmName)
    {
        $this->realmName = $realmName;
    }

    public function start(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_Security_Core_Exception_AuthenticationException $authException = null)
    {
        $response = new Symfony_Component_HttpFoundation_Response();
        $response->headers->set('WWW-Authenticate', sprintf('Basic realm="%s"', $this->realmName));
        $response->setStatusCode(401);

        return $response;
    }
}
