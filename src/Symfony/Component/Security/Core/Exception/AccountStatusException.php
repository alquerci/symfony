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
 * AccountStatusException is the base class for authentication exceptions
 * caused by the user account status.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
abstract class Symfony_Component_Security_Core_Exception_AccountStatusException extends Symfony_Component_Security_Core_Exception_AuthenticationException
{
    private $user;

    /**
     * Get the user.
     *
     * @return Symfony_Component_Security_Core_User_UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the user.
     *
     * @param Symfony_Component_Security_Core_User_UserInterface $user
     */
    public function setUser(Symfony_Component_Security_Core_User_UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->user,
            parent::serialize(),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($str)
    {
        list($this->user, $parentData) = unserialize($str);

        parent::unserialize($parentData);
    }
}
