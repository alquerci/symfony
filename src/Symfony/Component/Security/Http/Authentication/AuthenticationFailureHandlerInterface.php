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
 * Interface for custom authentication failure handlers.
 *
 * If you want to customize the failure handling process, instead of
 * overwriting the respective listener globally, you can set a custom failure
 * handler which implements this interface.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface Symfony_Component_Security_Http_Authentication_AuthenticationFailureHandlerInterface
{
    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Symfony_Component_HttpFoundation_Request                 $request
     * @param Symfony_Component_Security_Core_Exception_AuthenticationException $exception
     *
     * @return Symfony_Component_HttpFoundation_Response The response to return, never null
     */
    public function onAuthenticationFailure(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_Security_Core_Exception_AuthenticationException $exception);
}
