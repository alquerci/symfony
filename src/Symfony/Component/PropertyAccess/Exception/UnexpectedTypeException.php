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
 * Thrown when a value does not match an expected type.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_PropertyAccess_Exception_UnexpectedTypeException extends Symfony_Component_PropertyAccess_Exception_RuntimeException
{
    public function __construct($value, $expectedType)
    {
        parent::__construct(sprintf('Expected argument of type "%s", "%s" given', $expectedType, is_object($value) ? get_class($value) : gettype($value)));
    }
}
