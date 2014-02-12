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
 * UnauthorizedHttpException.
 *
 * @author Ben Ramsey <ben@benramsey.com>
 */
class Symfony_Component_HttpKernel_Exception_UnauthorizedHttpException extends Symfony_Component_HttpKernel_Exception_HttpException
{
    /**
     * Constructor.
     *
     * @param string     $challenge WWW-Authenticate challenge string
     * @param string     $message   The internal exception message
     * @param Exception  $previous  The previous exception
     * @param integer    $code      The internal exception code
     */
    public function __construct($challenge, $message = null, Exception $previous = null, $code = 0)
    {
        $headers = array('WWW-Authenticate' => $challenge);

        parent::__construct(401, $message, $previous, $headers, $code);
    }
}
