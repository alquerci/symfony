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
 * This handler clears the passed cookies when a user logs out.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Security_Http_Logout_CookieClearingLogoutHandler implements Symfony_Component_Security_Http_Logout_LogoutHandlerInterface
{
    private $cookies;

    /**
     * Constructor.
     *
     * @param array $cookies An array of cookie names to unset
     */
    public function __construct(array $cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * Implementation for the LogoutHandlerInterface. Deletes all requested cookies.
     *
     * @param Symfony_Component_HttpFoundation_Request        $request
     * @param Symfony_Component_HttpFoundation_Response       $response
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token
     */
    public function logout(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_HttpFoundation_Response $response, Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        foreach ($this->cookies as $cookieName => $cookieData) {
            $response->headers->clearCookie($cookieName, $cookieData['path'], $cookieData['domain']);
        }
    }
}
