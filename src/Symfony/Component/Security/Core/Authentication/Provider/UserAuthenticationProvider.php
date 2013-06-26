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
 * UserProviderInterface retrieves users for UsernamePasswordToken tokens.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Symfony_Component_Security_Core_Authentication_Provider_UserAuthenticationProvider implements Symfony_Component_Security_Core_Authentication_Provider_AuthenticationProviderInterface
{
    private $hideUserNotFoundExceptions;
    private $userChecker;
    private $providerKey;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Security_Core_User_UserCheckerInterface $userChecker                An UserCheckerInterface interface
     * @param string               $providerKey                A provider key
     * @param Boolean              $hideUserNotFoundExceptions Whether to hide user not found exception or not
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Symfony_Component_Security_Core_User_UserCheckerInterface $userChecker, $providerKey, $hideUserNotFoundExceptions = true)
    {
        if (empty($providerKey)) {
            throw new InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->userChecker = $userChecker;
        $this->providerKey = $providerKey;
        $this->hideUserNotFoundExceptions = $hideUserNotFoundExceptions;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        $username = $token->getUsername();
        if (empty($username)) {
            $username = 'NONE_PROVIDED';
        }

        try {
            $user = $this->retrieveUser($username, $token);
        } catch (Symfony_Component_Security_Core_Exception_UsernameNotFoundException $notFound) {
            if ($this->hideUserNotFoundExceptions) {
                throw new Symfony_Component_Security_Core_Exception_BadCredentialsException('Bad credentials', 0, $notFound);
            }
            $notFound->setUsername($username);

            throw $notFound;
        }

        if (!$user instanceof Symfony_Component_Security_Core_User_UserInterface) {
            throw new Symfony_Component_Security_Core_Exception_AuthenticationServiceException('retrieveUser() must return a UserInterface.');
        }

        try {
            $this->userChecker->checkPreAuth($user);
            $this->checkAuthentication($user, $token);
            $this->userChecker->checkPostAuth($user);
        } catch (Symfony_Component_Security_Core_Exception_BadCredentialsException $e) {
            if ($this->hideUserNotFoundExceptions) {
                throw new Symfony_Component_Security_Core_Exception_BadCredentialsException('Bad credentials', 0, $e);
            }

            throw $e;
        }

        $authenticatedToken = new Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken($user, $token->getCredentials(), $this->providerKey, $user->getRoles());
        $authenticatedToken->setAttributes($token->getAttributes());

        return $authenticatedToken;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        return $token instanceof Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken && $this->providerKey === $token->getProviderKey();
    }

    /**
     * Retrieves the user from an implementation-specific location.
     *
     * @param string                $username The username to retrieve
     * @param Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken $token    The Token
     *
     * @return Symfony_Component_Security_Core_User_UserInterface The user
     *
     * @throws Symfony_Component_Security_Core_Exception_AuthenticationException if the credentials could not be validated
     */
    abstract protected function retrieveUser($username, Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken $token);

    /**
     * Does additional checks on the user and token (like validating the
     * credentials).
     *
     * @param Symfony_Component_Security_Core_User_UserInterface         $user  The retrieved UserInterface instance
     * @param Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken $token The UsernamePasswordToken token to be authenticated
     *
     * @throws Symfony_Component_Security_Core_Exception_AuthenticationException if the credentials could not be validated
     */
    abstract protected function checkAuthentication(Symfony_Component_Security_Core_User_UserInterface $user, Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken $token);
}
