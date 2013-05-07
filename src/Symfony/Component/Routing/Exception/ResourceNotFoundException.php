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
 * The resource was not found.
 *
 * This exception should trigger an HTTP 404 response in your application code.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 *
 * @api
 */
class Symfony_Component_Routing_Exception_ResourceNotFoundException extends RuntimeException implements Symfony_Component_Routing_Exception_ExceptionInterface
{
    /**
     * @var Exception
     */
    private $previous;

    /**
     * @param string     $message   The Exception message to throw. [Optional]
     * @param integer    $code      The error code                  [Optional]
     * @param Exception  $previous  A previous exception            [Optional]
     */
    public function __construct($message = '', $code = null, Exception $previous = null)
    {
        if (method_exists($this, 'getPrevious')) {
            parent::__construct($message, $code, $previous);
        } else {
            $this->previous = $previous;
            parent::__construct($message, $code);
        }
    }

    public function __call($name, array $arguments)
    {
        switch ($name) {
            case 'getPrevious':
                return $this->previous;
            default:
        }

        throw new BadMethodCallException(sprintf('Call an undefined method %s->%s(%s)',
            get_class($this),
            $name,
            implode(', ', array_map('gettype', $arguments))
        ));
    }
}
