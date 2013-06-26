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
 * Handler for clearing invalidating the current session.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Security_Http_Logout_SessionLogoutHandler implements Symfony_Component_Security_Http_Logout_LogoutHandlerInterface
{
    /**
     * Invalidate the current session
     *
     * @param Symfony_Component_HttpFoundation_Request        $request
     * @param Symfony_Component_HttpFoundation_Response       $response
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token
     */
    public function logout(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_HttpFoundation_Response $response, Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        $request->getSession()->invalidate();
    }
}
