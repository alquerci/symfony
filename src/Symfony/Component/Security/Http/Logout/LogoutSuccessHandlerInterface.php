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
 * LogoutSuccesshandlerInterface.
 *
 * In contrast to the LogoutHandlerInterface, this interface can return a response
 * which is then used instead of the default behavior.
 *
 * If you want to only perform some logout related clean-up task, use the
 * LogoutHandlerInterface instead.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface Symfony_Component_Security_Http_Logout_LogoutSuccessHandlerInterface
{
    /**
     * Creates a Response object to send upon a successful logout.
     *
     * @param Symfony_Component_HttpFoundation_Request $request
     *
     * @return Symfony_Component_HttpFoundation_Response never null
     */
    public function onLogoutSuccess(Symfony_Component_HttpFoundation_Request $request);
}
