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
 * InMemoryUserProvider is a simple non persistent user provider.
 *
 * Useful for testing, demonstration, prototyping, and for simple needs
 * (a backend with a unique admin for instance)
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Core_User_InMemoryUserProvider implements Symfony_Component_Security_Core_User_UserProviderInterface
{
    private $users;

    /**
     * Constructor.
     *
     * The user array is a hash where the keys are usernames and the values are
     * an array of attributes: 'password', 'enabled', and 'roles'.
     *
     * @param array $users An array of users
     */
    public function __construct(array $users = array())
    {
        foreach ($users as $username => $attributes) {
            $password = isset($attributes['password']) ? $attributes['password'] : null;
            $enabled = isset($attributes['enabled']) ? $attributes['enabled'] : true;
            $roles = isset($attributes['roles']) ? $attributes['roles'] : array();
            $user = new Symfony_Component_Security_Core_User_User($username, $password, $roles, $enabled, true, true, true);

            $this->createUser($user);
        }
    }

    /**
     * Adds a new User to the provider.
     *
     * @param Symfony_Component_Security_Core_User_UserInterface $user A UserInterface instance
     *
     * @throws LogicException
     */
    public function createUser(Symfony_Component_Security_Core_User_UserInterface $user)
    {
        if (isset($this->users[strtolower($user->getUsername())])) {
            throw new LogicException('Another user with the same username already exist.');
        }

        $this->users[strtolower($user->getUsername())] = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        if (!isset($this->users[strtolower($username)])) {
            $ex = new Symfony_Component_Security_Core_Exception_UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            $ex->setUsername($username);

            throw $ex;
        }

        $user = $this->users[strtolower($username)];

        return new Symfony_Component_Security_Core_User_User($user->getUsername(), $user->getPassword(), $user->getRoles(), $user->isEnabled(), $user->isAccountNonExpired(),
                $user->isCredentialsNonExpired(), $user->isAccountNonLocked());
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(Symfony_Component_Security_Core_User_UserInterface $user)
    {
        if (!$user instanceof Symfony_Component_Security_Core_User_User) {
            throw new Symfony_Component_Security_Core_Exception_UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Symfony_Component_Security_Core_User_User';
    }
}
