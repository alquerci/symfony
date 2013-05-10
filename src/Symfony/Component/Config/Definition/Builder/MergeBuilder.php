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
 * This class builds merge conditions.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Config_Definition_Builder_MergeBuilder
{
    protected $node;
    public $allowFalse;
    public $allowOverwrite;

    /**
     * Constructor
     *
     * @param Symfony_Component_Config_Definition_Builder_NodeDefinition $node The related node
     */
    public function __construct(Symfony_Component_Config_Definition_Builder_NodeDefinition $node)
    {
        $this->node = $node;
        $this->allowFalse = false;
        $this->allowOverwrite = true;
    }

    /**
     * Sets whether the node can be unset.
     *
     * @param Boolean $allow
     *
     * @return MergeBuilder
     */
    public function allowUnset($allow = true)
    {
        $this->allowFalse = $allow;

        return $this;
    }

    /**
     * Sets whether the node can be overwritten.
     *
     * @param Boolean $deny Whether the overwriting is forbidden or not
     *
     * @return MergeBuilder
     */
    public function denyOverwrite($deny = true)
    {
        $this->allowOverwrite = !$deny;

        return $this;
    }

    /**
     * Returns the related node.
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     */
    public function end()
    {
        return $this->node;
    }
}
