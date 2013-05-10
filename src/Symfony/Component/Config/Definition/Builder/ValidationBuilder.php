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
 * This class builds validation conditions.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class Symfony_Component_Config_Definition_Builder_ValidationBuilder
{
    protected $node;
    public $rules;

    /**
     * Constructor
     *
     * @param Symfony_Component_Config_Definition_Builder_NodeDefinition $node The related node
     */
    public function __construct(Symfony_Component_Config_Definition_Builder_NodeDefinition $node)
    {
        $this->node = $node;

        $this->rules = array();
    }

    /**
     * Registers a closure to run as normalization or an expression builder to build it if null is provided.
     *
     * @param callable $closure
     *
     * @return Symfony_Component_Config_Definition_Builder_ExprBuilder|Symfony_Component_Config_Definition_Builder_ValidationBuilder
     */
    public function rule($closure = null)
    {
        if (null !== $closure) {
            assert(is_callable($closure));
        }

        if (null !== $closure) {
            $this->rules[] = $closure;

            return $this;
        }

        return $this->rules[] = new Symfony_Component_Config_Definition_Builder_ExprBuilder($this->node);
    }
}
