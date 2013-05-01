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
 * This class provides a fluent interface for defining a float node.
 *
 * @author Jeanmonod David <david.jeanmonod@gmail.com>
 */
class Symfony_Component_Config_Definition_Builder_FloatNodeDefinition extends Symfony_Component_Config_Definition_Builder_NumericNodeDefinition
{
    /**
     * Instantiates a Node.
     *
     * @return Symfony_Component_Config_Definition_FloatNode The node
     */
    protected function instantiateNode()
    {
        return new Symfony_Component_Config_Definition_FloatNode($this->name, $this->parent, $this->min, $this->max);
    }
}
