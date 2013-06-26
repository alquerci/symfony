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
 * Processes a pre-authenticated authentication request.
 *
 * This authentication provider will not perform any checks on authentication
 * requests, as they should already be pre-authenticated. However, the
 * UserProviderInterface implementation may still throw a
 * UsernameNotFoundException, for example.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Core_Authentication_Provider_PreAuthenticatedAuthenticationProvider implements Symfony_Component_Security_Core_Authentication_Provider_AuthenticationProviderInterface
{
    private $userProvider;
    private $userChecker;
    private $providerKey;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Security_Core_User_UserProviderInterface $userProvider An UserProviderInterface instance
     * @param Symfony_Component_Security_Core_User_UserCheckerInterface  $userChecker  An UserCheckerInterface instance
     * @param string                $providerKey  The provider key
     */
    public function __construct(Symfony_Component_Security_Core_User_UserProviderInterface $userProvider, Symfony_Component_Security_Core_User_UserCheckerInterface $userChecker, $providerKey)
    {
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
        $this->providerKey = $providerKey;
    }

     /**
      * {@inheritdoc}
      */
     public function authenticate(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
     {
         if (!$this->supports($token)) {
             return null;
         }

        if (!$user = $token->getUser()) {
            throw new Symfony_Component_Security_Core_Exception_BadCredentialsException('No pre-authenticated principal found in request.');
        }
/*
        if (null === $token->getCredentials()) {
            throw new Symfony_Component_Security_Core_Exception_BadCredentialsException('No pre-authenticated credentials found in request.');
        }
*/
        $user = $this->userProvider->loadUserByUsername($user);

        $this->userChecker->checkPostAuth($user);

        $authenticatedToken = new Symfony_Component_Security_Core_Authentication_Token_PreAuthenticatedToken($user, $token->getCredentials(), $this->providerKey, $user->getRoles());
        $authenticatedToken->setAttributes($token->getAttributes());

        return $authenticatedToken;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        return $token instanceof Symfony_Component_Security_Core_Authentication_Token_PreAuthenticatedToken && $this->providerKey === $token->getProviderKey();
    }
}
