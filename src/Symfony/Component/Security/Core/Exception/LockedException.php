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
 * LockedException is thrown if the user account is locked.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class Symfony_Component_Security_Core_Exception_LockedException extends Symfony_Component_Security_Core_Exception_AccountStatusException
{
    /**
     * {@inheritDoc}
     */
    public function getMessageKey()
    {
        return 'Account is locked.';
    }
}
