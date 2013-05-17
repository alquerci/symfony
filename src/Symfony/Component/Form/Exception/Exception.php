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
 * Base exception class.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @deprecated This class is a replacement for when class FormException was
 *             used previously. It should not be used and will be removed.
 *             Occurrences of this class should be replaced by more specialized
 *             exception classes, preferably derived from SPL exceptions.
 */
class Symfony_Component_Form_Exception_Exception extends Exception implements Symfony_Component_Form_Exception_ExceptionInterface
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
