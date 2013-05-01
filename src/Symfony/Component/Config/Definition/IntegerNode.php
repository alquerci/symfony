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
 * This node represents an integer value in the config tree.
 *
 * @author Jeanmonod David <david.jeanmonod@gmail.com>
 */
class Symfony_Component_Config_Definition_IntegerNode extends Symfony_Component_Config_Definition_NumericNode
{
    /**
     * {@inheritDoc}
     */
    protected function validateType($value)
    {
        if (!is_int($value)) {
            $ex = new Symfony_Component_Config_Definition_Exception_InvalidTypeException(sprintf('Invalid type for path "%s". Expected int, but got %s.', $this->getPath(), gettype($value)));
            $ex->setPath($this->getPath());

            throw $ex;
        }
    }
}
