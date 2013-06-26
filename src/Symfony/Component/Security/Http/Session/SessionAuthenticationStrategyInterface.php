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
 * SessionAuthenticationStrategyInterface
 *
 * Implementation are responsible for updating the session after an interactive
 * authentication attempt was successful.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface Symfony_Component_Security_Http_Session_SessionAuthenticationStrategyInterface
{
    /**
     * This performs any necessary changes to the session.
     *
     * This method is called before the SecurityContext is populated with a
     * Token, and only by classes inheriting from AbstractAuthenticationListener.
     *
     * @param Symfony_Component_HttpFoundation_Request        $request
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token
     */
    public function onAuthentication(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token);
}
