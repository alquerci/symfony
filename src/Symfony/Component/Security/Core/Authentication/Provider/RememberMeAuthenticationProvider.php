<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Core_Authentication_Provider_RememberMeAuthenticationProvider implements Symfony_Component_Security_Core_Authentication_Provider_AuthenticationProviderInterface
{
    private $userChecker;
    private $key;
    private $providerKey;

    public function __construct(Symfony_Component_Security_Core_User_UserCheckerInterface $userChecker, $key, $providerKey)
    {
        $this->userChecker = $userChecker;
        $this->key = $key;
        $this->providerKey = $providerKey;
    }

    public function authenticate(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return;
        }

        if ($this->key !== $token->getKey()) {
            throw new Symfony_Component_Security_Core_Exception_BadCredentialsException('The presented key does not match.');
        }

        $user = $token->getUser();
        $this->userChecker->checkPostAuth($user);

        $authenticatedToken = new Symfony_Component_Security_Core_Authentication_Token_RememberMeToken($user, $this->providerKey, $this->key);
        $authenticatedToken->setAttributes($token->getAttributes());

        return $authenticatedToken;
    }

    public function supports(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        return $token instanceof Symfony_Component_Security_Core_Authentication_Token_RememberMeToken && $token->getProviderKey() === $this->providerKey;
    }
}
