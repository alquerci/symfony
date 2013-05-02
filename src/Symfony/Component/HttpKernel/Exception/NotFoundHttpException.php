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
 * NotFoundHttpException.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_HttpKernel_Exception_NotFoundHttpException extends Symfony_Component_HttpKernel_Exception_HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message  The internal exception message
     * @param Exception $previous The previous exception
     * @param integer    $code     The internal exception code
     */
    public function __construct($message = null, Exception $previous = null, $code = 0)
    {
        parent::__construct(404, $message, $previous, array(), $code);
    }
}
