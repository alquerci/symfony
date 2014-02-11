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
 * PreconditionRequiredHttpException.
 *
 * @author Ben Ramsey <ben@benramsey.com>
 * @see http://tools.ietf.org/html/rfc6585
 */
class Symfony_Component_HttpKernel_Exception_PreconditionRequiredHttpException extends Symfony_Component_HttpKernel_Exception_HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message   The internal exception message
     * @param Exception  $previous  The previous exception
     * @param integer    $code      The internal exception code
     */
    public function __construct($message = null, Exception $previous = null, $code = 0)
    {
        parent::__construct(428, $message, $previous, array(), $code);
    }
}
