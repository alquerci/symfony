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
 * AuthenticationManagerInterface is the interface for authentication managers,
 * which process Token authentication.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface
{
    /**
     * Attempts to authenticates a TokenInterface object.
     *
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token The TokenInterface instance to authenticate
     *
     * @return Symfony_Component_Security_Core_Authentication_Token_TokenInterface An authenticated TokenInterface instance, never null
     *
     * @throws Symfony_Component_Security_Core_Exception_AuthenticationException if the authentication fails
     */
    public function authenticate(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token);
}
