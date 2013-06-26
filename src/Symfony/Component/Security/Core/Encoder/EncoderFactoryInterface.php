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
 * EncoderFactoryInterface to support different encoders for different accounts.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface Symfony_Component_Security_Core_Encoder_EncoderFactoryInterface
{
    /**
     * Returns the password encoder to use for the given account.
     *
     * @param Symfony_Component_Security_Core_User_UserInterface|string $user A UserInterface instance or a class name
     *
     * @return Symfony_Component_Security_Core_Encoder_PasswordEncoderInterface
     *
     * @throws RuntimeException when no password encoder could be found for the user
     */
    public function getEncoder($user);
}
