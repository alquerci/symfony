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
 * This exception is thrown when an account is reloaded from a provider which
 * doesn't support the passed implementation of UserInterface.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Security_Core_Exception_UnsupportedUserException extends Symfony_Component_Security_Core_Exception_AuthenticationServiceException
{
}
