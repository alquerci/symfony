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
 * Fatal Error Exception.
 *
 * @author Konstanton Myakshin <koc-dp@yandex.ru>
 */
class Symfony_Component_HttpKernel_Exception_FatalErrorException extends ErrorException
{
    /**
     * @var Exception
     */
    private $previous;

    /**
     * @param string     $message   The Exception message to throw.                [Optional]
     * @param integer    $code      The error code                                 [Optional]
     * @param integer    $severity  The severity level of the exception.           [Optional]
     * @param string     $filename  The filename where the exception is thrown.    [Optional]
     * @param integer    $lineno    The line number where the exception is thrown. [Optional]
     * @param Exception  $previous  A previous exception                           [Optional]
     */
    public function __construct($message = '', $code = null, $severity = 1, $filename = __FILE__, $lineno = __LINE__, Exception $previous = null)
    {
        if (method_exists($this, 'getPrevious')) {
            parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
        } else {
            $this->previous = $previous;
            parent::__construct($message, $code, $severity, $filename, $lineno);
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
