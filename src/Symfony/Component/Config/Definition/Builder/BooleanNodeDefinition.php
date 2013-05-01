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
class Symfony_Component_Config_Definition_Builder_BooleanNodeDefinition extends Symfony_Component_Config_Definition_Builder_ScalarNodeDefinition
{
    /**
     * {@inheritDoc}
     */
    public function __construct($name, Symfony_Component_Config_Definition_Builder_NodeParentInterface $parent = null)
    {
        parent::__construct($name, $parent);

        $this->nullEquivalent = true;
    }

    /**
     * Instantiate a Node
     *
     * @return Symfony_Component_Config_Definition_BooleanNode The node
     */
    protected function instantiateNode()
    {
        return new Symfony_Component_Config_Definition_BooleanNode($this->name, $this->parent);
    }

}
