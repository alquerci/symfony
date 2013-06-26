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
 * AnonymousAuthenticationProvider validates AnonymousToken instances.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Core_Authentication_Provider_AnonymousAuthenticationProvider implements Symfony_Component_Security_Core_Authentication_Provider_AuthenticationProviderInterface
{
    private $key;

    /**
     * Constructor.
     *
     * @param string $key The key shared with the authentication token
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        if ($this->key !== $token->getKey()) {
            throw new Symfony_Component_Security_Core_Exception_BadCredentialsException('The Token does not contain the expected key.');
        }

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        return $token instanceof Symfony_Component_Security_Core_Authentication_Token_AnonymousToken;
    }
}
