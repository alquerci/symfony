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
 * This exception is thrown when no session is available.
 *
 * Possible reasons for this are:
 *
 *     a) The session timed out because the user waited too long.
 *     b) The user has disabled cookies, and a new session is started on each
 *        request.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class Symfony_Component_Security_Core_Exception_SessionUnavailableException extends Symfony_Component_Security_Core_Exception_AuthenticationException
{
    /**
     * {@inheritDoc}
     */
    public function getMessageKey()
    {
        return 'No session available, it either timed out or cookies are not enabled.';
    }
}
