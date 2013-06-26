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
 * Chain User Provider.
 *
 * This provider calls several leaf providers in a chain until one is able to
 * handle the request.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Security_Core_User_ChainUserProvider implements Symfony_Component_Security_Core_User_UserProviderInterface
{
    private $providers;

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        foreach ($this->providers as $provider) {
            try {
                return $provider->loadUserByUsername($username);
            } catch (Symfony_Component_Security_Core_Exception_UsernameNotFoundException $notFound) {
                // try next one
            }
        }

        $ex = new Symfony_Component_Security_Core_Exception_UsernameNotFoundException(sprintf('There is no user with name "%s".', $username));
        $ex->setUsername($username);
        throw $ex;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(Symfony_Component_Security_Core_User_UserInterface $user)
    {
        $supportedUserFound = false;

        foreach ($this->providers as $provider) {
            try {
                return $provider->refreshUser($user);
            } catch (Symfony_Component_Security_Core_Exception_UnsupportedUserException $unsupported) {
                // try next one
            } catch (Symfony_Component_Security_Core_Exception_UsernameNotFoundException $notFound) {
                $supportedUserFound = true;
                // try next one
            }
        }

        if ($supportedUserFound) {
            $ex = new Symfony_Component_Security_Core_Exception_UsernameNotFoundException(sprintf('There is no user with name "%s".', $user->getUsername()));
            $ex->setUsername($user->getUsername());
            throw $ex;
        } else {
            throw new Symfony_Component_Security_Core_Exception_UnsupportedUserException(sprintf('The account "%s" is not supported.', get_class($user)));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supportsClass($class)) {
                return true;
            }
        }

        return false;
    }
}
