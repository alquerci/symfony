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
 * The resource was found but the request method is not allowed.
 *
 * This exception should trigger an HTTP 405 response in your application code.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 *
 * @api
 */
class Symfony_Component_Routing_Exception_MethodNotAllowedException extends RuntimeException implements Symfony_Component_Routing_Exception_ExceptionInterface
{
    /**
     * @var array
     */
    protected $allowedMethods = array();

    /**
     * @var Exception
     */
    private $previous;

    public function __construct(array $allowedMethods, $message = null, $code = 0, Exception $previous = null)
    {
        $this->allowedMethods = array_map('strtoupper', $allowedMethods);

        if (method_exists($this, 'getPrevious')) {
            parent::__construct($message, $code, $previous);
        } else {
            $this->previous = $previous;
            parent::__construct($message, $code);
        }
    }

    /**
     * Gets the allowed HTTP methods.
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return $this->allowedMethods;
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
