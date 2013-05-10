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
 * This class provides a fluent interface for defining a node.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Config_Definition_Builder_VariableNodeDefinition extends Symfony_Component_Config_Definition_Builder_NodeDefinition
{
    /**
     * Instantiate a Node
     *
     * @return Symfony_Component_Config_Definition_VariableNode The node
     */
    protected function instantiateNode()
    {
        return new Symfony_Component_Config_Definition_VariableNode($this->name, $this->parent);
    }

    /**
     * {@inheritDoc}
     */
    protected function createNode()
    {
        $node = $this->instantiateNode();

        if (null !== $this->normalization) {
            $node->setNormalizationClosures($this->normalization->before);
        }

        if (null !== $this->merge) {
            $node->setAllowOverwrite($this->merge->allowOverwrite);
        }

        if (true === $this->default) {
            $node->setDefaultValue($this->defaultValue);
        }

        if (false === $this->allowEmptyValue) {
            $node->setAllowEmptyValue($this->allowEmptyValue);
        }

        $node->addEquivalentValue(null, $this->nullEquivalent);
        $node->addEquivalentValue(true, $this->trueEquivalent);
        $node->addEquivalentValue(false, $this->falseEquivalent);
        $node->setRequired($this->required);

        if (null !== $this->validation) {
            $node->setFinalValidationClosures($this->validation->rules);
        }

        return $node;
    }

}
