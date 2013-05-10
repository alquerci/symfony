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
 * This node represents a numeric value in the config tree
 *
 * @author David Jeanmonod <david.jeanmonod@gmail.com>
 */
class Symfony_Component_Config_Definition_NumericNode extends Symfony_Component_Config_Definition_ScalarNode
{
    protected $min;
    protected $max;

    public function __construct($name, Symfony_Component_Config_Definition_NodeInterface $parent = null, $min = null, $max = null)
    {
        parent::__construct($name, $parent);
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * {@inheritDoc}
     */
    protected function finalizeValue($value)
    {
        $value = parent::finalizeValue($value);

        $errorMsg = null;
        if (isset($this->min) && $value < $this->min) {
            $errorMsg = sprintf('The value %s is too small for path "%s". Should be greater than: %s', $value, $this->getPath(), $this->min);
        }
        if (isset($this->max) && $value > $this->max) {
            $errorMsg = sprintf('The value %s is too big for path "%s". Should be less than: %s', $value, $this->getPath(), $this->max);
        }
        if (isset($errorMsg)) {
            $ex = new Symfony_Component_Config_Definition_Exception_InvalidConfigurationException($errorMsg);
            $ex->setPath($this->getPath());
            throw $ex;
        }

        return $value;
    }
}
