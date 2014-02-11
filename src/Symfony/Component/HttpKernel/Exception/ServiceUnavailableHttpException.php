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
 * ServiceUnavailableHttpException.
 *
 * @author Ben Ramsey <ben@benramsey.com>
 */
class Symfony_Component_HttpKernel_Exception_ServiceUnavailableHttpException extends Symfony_Component_HttpKernel_Exception_HttpException
{
    /**
     * Constructor.
     *
     * @param int|string  $retryAfter The number of seconds or HTTP-date after which the request may be retried
     * @param string      $message    The internal exception message
     * @param Exception   $previous   The previous exception
     * @param integer     $code       The internal exception code
     */
    public function __construct($retryAfter = null, $message = null, Exception $previous = null, $code = 0)
    {
        $headers = array();
        if ($retryAfter) {
            $headers = array('Retry-After' => $retryAfter);
        }

        parent::__construct(503, $message, $previous, $headers, $code);
    }
}
