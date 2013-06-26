<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Http_Event_InteractiveLoginEvent extends Symfony_Component_EventDispatcher_Event
{
    private $request;

    private $authenticationToken;

    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpFoundation_Request        $request             A Request instance
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $authenticationToken A TokenInterface instance
     */
    public function __construct(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_Security_Core_Authentication_Token_TokenInterface $authenticationToken)
    {
        $this->request = $request;
        $this->authenticationToken = $authenticationToken;
    }

    /**
     * Gets the request.
     *
     * @return Symfony_Component_HttpFoundation_Request A Request instance
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Gets the authentication token.
     *
     * @return Symfony_Component_Security_Core_Authentication_Token_TokenInterface A TokenInterface instance
     */
    public function getAuthenticationToken()
    {
        return $this->authenticationToken;
    }
}
