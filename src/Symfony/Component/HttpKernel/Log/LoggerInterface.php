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
 * LoggerInterface.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @deprecated since 2.2, to be removed in 3.0. Type-hint Psr_Log_LoggerInterface instead.
 * @api
 */
interface Symfony_Component_HttpKernel_Log_LoggerInterface extends Psr_Log_LoggerInterface
{
    /**
     * @api
     * @deprecated since 2.2, to be removed in 3.0. Use emergency() which is PSR-3 compatible.
     */
    public function emerg($message, array $context = array());

    /**
     * @api
     * @deprecated since 2.2, to be removed in 3.0. Use critical() which is PSR-3 compatible.
     */
    public function crit($message, array $context = array());

    /**
     * @api
     * @deprecated since 2.2, to be removed in 3.0. Use error() which is PSR-3 compatible.
     */
    public function err($message, array $context = array());

    /**
     * @api
     * @deprecated since 2.2, to be removed in 3.0. Use warning() which is PSR-3 compatible.
     */
    public function warn($message, array $context = array());
}
