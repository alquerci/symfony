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
 * This node represents a value of variable type in the config tree.
 *
 * This node is intended for values of arbitrary type.
 * Any PHP type is accepted as a value.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class Symfony_Component_Config_Definition_VariableNode extends Symfony_Component_Config_Definition_BaseNode implements Symfony_Component_Config_Definition_PrototypeNodeInterface
{
    protected $defaultValueSet = false;
    protected $defaultValue;
    protected $allowEmptyValue = true;

    /**
     * {@inheritDoc}
     */
    public function setDefaultValue($value)
    {
        $this->defaultValueSet = true;
        $this->defaultValue = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function hasDefaultValue()
    {
        return $this->defaultValueSet;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultValue()
    {
        $value = $this->defaultValue;

        if ($value instanceof Closure) {
            return $value();
        }

        if (is_string($value) && is_callable($value)) {
            $definedFunctions = get_defined_functions();

            if (!in_array($value, $definedFunctions['user'], true)
                && !in_array($value, $definedFunctions['internal'], true)
            ) {
                return $value();
            }
        }

        return $value;
    }

    /**
     * Sets if this node is allowed to have an empty value.
     *
     * @param Boolean $boolean True if this entity will accept empty values.
     */
    public function setAllowEmptyValue($boolean)
    {
        $this->allowEmptyValue = (Boolean) $boolean;
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    protected function validateType($value)
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function finalizeValue($value)
    {
        if (!$this->allowEmptyValue && empty($value)) {
            $ex = new Symfony_Component_Config_Definition_Exception_InvalidConfigurationException(sprintf(
                'The path "%s" cannot contain an empty value, but got %s.',
                $this->getPath(),
                json_encode($value)
            ));
            $ex->setPath($this->getPath());

            throw $ex;
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    protected function normalizeValue($value)
    {
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    protected function mergeValues($leftSide, $rightSide)
    {
        return $rightSide;
    }
}
