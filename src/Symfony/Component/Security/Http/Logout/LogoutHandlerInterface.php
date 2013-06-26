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
 * Interface that needs to be implemented by LogoutHandlers.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface Symfony_Component_Security_Http_Logout_LogoutHandlerInterface
{
    /**
     * This method is called by the LogoutListener when a user has requested
     * to be logged out. Usually, you would unset session variables, or remove
     * cookies, etc.
     *
     * @param Symfony_Component_HttpFoundation_Request        $request
     * @param Symfony_Component_HttpFoundation_Response       $response
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token
     */
    public function logout(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_HttpFoundation_Response $response, Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token);
}
