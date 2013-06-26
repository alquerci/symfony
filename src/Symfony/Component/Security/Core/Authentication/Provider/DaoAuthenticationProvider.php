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
 * DaoAuthenticationProvider uses a UserProviderInterface to retrieve the user
 * for a UsernamePasswordToken.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Core_Authentication_Provider_DaoAuthenticationProvider extends Symfony_Component_Security_Core_Authentication_Provider_UserAuthenticationProvider
{
    private $encoderFactory;
    private $userProvider;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Security_Core_User_UserProviderInterface   $userProvider               An UserProviderInterface instance
     * @param Symfony_Component_Security_Core_User_UserCheckerInterface    $userChecker                An UserCheckerInterface instance
     * @param string                  $providerKey                The provider key
     * @param Symfony_Component_Security_Core_Encoder_EncoderFactoryInterface $encoderFactory             An EncoderFactoryInterface instance
     * @param Boolean                 $hideUserNotFoundExceptions Whether to hide user not found exception or not
     */
    public function __construct(Symfony_Component_Security_Core_User_UserProviderInterface $userProvider, Symfony_Component_Security_Core_User_UserCheckerInterface $userChecker, $providerKey, Symfony_Component_Security_Core_Encoder_EncoderFactoryInterface $encoderFactory, $hideUserNotFoundExceptions = true)
    {
        parent::__construct($userChecker, $providerKey, $hideUserNotFoundExceptions);

        $this->encoderFactory = $encoderFactory;
        $this->userProvider = $userProvider;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkAuthentication(Symfony_Component_Security_Core_User_UserInterface $user, Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken $token)
    {
        $currentUser = $token->getUser();
        if ($currentUser instanceof Symfony_Component_Security_Core_User_UserInterface) {
            if ($currentUser->getPassword() !== $user->getPassword()) {
                throw new Symfony_Component_Security_Core_Exception_BadCredentialsException('The credentials were changed from another session.');
            }
        } else {
            if ("" === ($presentedPassword = $token->getCredentials())) {
                throw new Symfony_Component_Security_Core_Exception_BadCredentialsException('The presented password cannot be empty.');
            }

            if (!$this->encoderFactory->getEncoder($user)->isPasswordValid($user->getPassword(), $presentedPassword, $user->getSalt())) {
                throw new Symfony_Component_Security_Core_Exception_BadCredentialsException('The presented password is invalid.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function retrieveUser($username, Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken $token)
    {
        $user = $token->getUser();
        if ($user instanceof Symfony_Component_Security_Core_User_UserInterface) {
            return $user;
        }

        try {
            $user = $this->userProvider->loadUserByUsername($username);

            if (!$user instanceof Symfony_Component_Security_Core_User_UserInterface) {
                throw new Symfony_Component_Security_Core_Exception_AuthenticationServiceException('The user provider must return a UserInterface object.');
            }

            return $user;
        } catch (Symfony_Component_Security_Core_Exception_UsernameNotFoundException $notFound) {
            $notFound->setUsername($username);
            throw $notFound;
        } catch (Exception $repositoryProblem) {
            $ex = new Symfony_Component_Security_Core_Exception_AuthenticationServiceException($repositoryProblem->getMessage(), 0, $repositoryProblem);
            $ex->setToken($token);
            throw $ex;
        }
    }
}
