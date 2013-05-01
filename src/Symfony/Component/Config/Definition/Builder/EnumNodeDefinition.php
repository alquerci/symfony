<?php

/**
 * Enum Node Definition.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Config_Definition_Builder_EnumNodeDefinition extends Symfony_Component_Config_Definition_Builder_ScalarNodeDefinition
{
    private $values;

    public function values(array $values)
    {
        $values = array_unique($values);

        if (count($values) <= 1) {
            throw new InvalidArgumentException('->values() must be called with at least two distinct values.');
        }

        $this->values = $values;

        return $this;
    }

    /**
     * Instantiate a Node
     *
     * @return Symfony_Component_Config_Definition_EnumNode The node
     *
     * @throws RuntimeException
     */
    protected function instantiateNode()
    {
        if (null === $this->values) {
            throw new RuntimeException('You must call ->values() on enum nodes.');
        }

        return new Symfony_Component_Config_Definition_EnumNode($this->name, $this->parent, $this->values);
    }
}
