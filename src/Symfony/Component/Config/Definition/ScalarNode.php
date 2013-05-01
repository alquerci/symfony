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
 * This node represents a scalar value in the config tree.
 *
 * The following values are considered scalars:
 *   * booleans
 *   * strings
 *   * null
 *   * integers
 *   * floats
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Config_Definition_ScalarNode extends Symfony_Component_Config_Definition_VariableNode
{
    /**
     * {@inheritDoc}
     */
    protected function validateType($value)
    {
        if (!is_scalar($value) && null !== $value) {
            $ex = new Symfony_Component_Config_Definition_Exception_InvalidTypeException(sprintf(
                'Invalid type for path "%s". Expected scalar, but got %s.',
                $this->getPath(),
                gettype($value)
            ));
            $ex->setPath($this->getPath());

            throw $ex;
        }
    }
}
