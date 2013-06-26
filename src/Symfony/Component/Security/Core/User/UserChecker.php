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
 * UserChecker checks the user account flags.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Core_User_UserChecker implements Symfony_Component_Security_Core_User_UserCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(Symfony_Component_Security_Core_User_UserInterface $user)
    {
        if (!$user instanceof Symfony_Component_Security_Core_User_AdvancedUserInterface) {
            return;
        }

        if (!$user->isCredentialsNonExpired()) {
            $ex = new Symfony_Component_Security_Core_Exception_CredentialsExpiredException('User credentials have expired.');
            $ex->setUser($user);
            throw $ex;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(Symfony_Component_Security_Core_User_UserInterface $user)
    {
        if (!$user instanceof Symfony_Component_Security_Core_User_AdvancedUserInterface) {
            return;
        }

        if (!$user->isAccountNonLocked()) {
            $ex = new Symfony_Component_Security_Core_Exception_LockedException('User account is locked.');
            $ex->setUser($user);
            throw $ex;
        }

        if (!$user->isEnabled()) {
            $ex = new Symfony_Component_Security_Core_Exception_DisabledException('User account is disabled.');
            $ex->setUser($user);
            throw $ex;
        }

        if (!$user->isAccountNonExpired()) {
            $ex = new Symfony_Component_Security_Core_Exception_AccountExpiredException('User account has expired.');
            $ex->setUser($user);
            throw $ex;
        }
    }
}
