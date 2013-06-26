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
 * LogoutException is thrown when the account cannot be logged out.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class Symfony_Component_Security_Core_Exception_LogoutException extends Symfony_Component_Security_Core_Exception_RuntimeException
{
    public function __construct($message = 'Logout Exception', Exception $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }
}
