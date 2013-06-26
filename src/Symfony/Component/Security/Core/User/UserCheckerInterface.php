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
 * UserCheckerInterface checks user account when authentication occurs.
 *
 * This should not be used to make authentication decisions.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Symfony_Component_Security_Core_User_UserCheckerInterface
{
    /**
     * Checks the user account before authentication.
     *
     * @param Symfony_Component_Security_Core_User_UserInterface $user a UserInterface instance
     */
    public function checkPreAuth(Symfony_Component_Security_Core_User_UserInterface $user);

    /**
     * Checks the user account after authentication.
     *
     * @param Symfony_Component_Security_Core_User_UserInterface $user a UserInterface instance
     */
    public function checkPostAuth(Symfony_Component_Security_Core_User_UserInterface $user);
}
