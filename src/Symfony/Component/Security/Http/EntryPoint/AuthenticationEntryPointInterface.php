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
 * AuthenticationEntryPointInterface is the interface used to start the
 * authentication scheme.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Symfony_Component_Security_Http_EntryPoint_AuthenticationEntryPointInterface
{
    /**
     * Starts the authentication scheme.
     *
     * @param Symfony_Component_HttpFoundation_Request                 $request       The request that resulted in an AuthenticationException
     * @param Symfony_Component_Security_Core_Exception_AuthenticationException $authException The exception that started the authentication process
     *
     * @return Response
     */
    public function start(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_Security_Core_Exception_AuthenticationException $authException = null);
}
