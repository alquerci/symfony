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
 * This event is dispatched on authentication failure.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Security_Core_Event_AuthenticationFailureEvent extends Symfony_Component_Security_Core_Event_AuthenticationEvent
{
    private $authenticationException;

    public function __construct(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token, Symfony_Component_Security_Core_Exception_AuthenticationException $ex)
    {
        parent::__construct($token);

        $this->authenticationException = $ex;
    }

    public function getAuthenticationException()
    {
        return $this->authenticationException;
    }
}
