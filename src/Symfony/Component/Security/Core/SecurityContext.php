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
 * SecurityContext is the main entry point of the Security component.
 *
 * It gives access to the token representing the current user authentication.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Security_Core_SecurityContext implements Symfony_Component_Security_Core_SecurityContextInterface
{
    private $token;
    private $accessDecisionManager;
    private $authenticationManager;
    private $alwaysAuthenticate;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface      $authenticationManager An AuthenticationManager instance
     * @param Symfony_Component_Security_Core_Authorization_AccessDecisionManagerInterface|null $accessDecisionManager An AccessDecisionManager instance
     * @param Boolean                             $alwaysAuthenticate
     */
    public function __construct(Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface $authenticationManager, Symfony_Component_Security_Core_Authorization_AccessDecisionManagerInterface $accessDecisionManager, $alwaysAuthenticate = false)
    {
        $this->authenticationManager = $authenticationManager;
        $this->accessDecisionManager = $accessDecisionManager;
        $this->alwaysAuthenticate = $alwaysAuthenticate;
    }

    /**
     * Checks if the attributes are granted against the current token.
     *
     * @throws Symfony_Component_Security_Core_Exception_AuthenticationCredentialsNotFoundException when the security context has no authentication token.
     *
     * @param mixed      $attributes
     * @param mixed|null $object
     *
     * @return Boolean
     */
    final public function isGranted($attributes, $object = null)
    {
        if (null === $this->token) {
            throw new Symfony_Component_Security_Core_Exception_AuthenticationCredentialsNotFoundException('The security context contains no authentication token. One possible reason may be that there is no firewall configured for this URL.');
        }

        if ($this->alwaysAuthenticate || !$this->token->isAuthenticated()) {
            $this->token = $this->authenticationManager->authenticate($this->token);
        }

        if (!is_array($attributes)) {
            $attributes = array($attributes);
        }

        return $this->accessDecisionManager->decide($this->token, $attributes, $object);
    }

    /**
     * Gets the currently authenticated token.
     *
     * @return Symfony_Component_Security_Core_Authentication_Token_TokenInterface|null A TokenInterface instance or null if no authentication information is available
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Sets the currently authenticated token.
     *
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token A TokenInterface token, or null if no further authentication information should be stored
     */
    public function setToken(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token = null)
    {
        $this->token = $token;
    }
}
