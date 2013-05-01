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
 * Represents an edge in your service graph.
 *
 * Value is typically a reference.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_DependencyInjection_Compiler_ServiceReferenceGraphEdge
{
    private $sourceNode;
    private $destNode;
    private $value;

    /**
     * Constructor.
     *
     * @param Symfony_Component_DependencyInjection_Compiler_ServiceReferenceGraphNode $sourceNode
     * @param Symfony_Component_DependencyInjection_Compiler_ServiceReferenceGraphNode $destNode
     * @param string                    $value
     */
    public function __construct(Symfony_Component_DependencyInjection_Compiler_ServiceReferenceGraphNode $sourceNode, Symfony_Component_DependencyInjection_Compiler_ServiceReferenceGraphNode $destNode, $value = null)
    {
        $this->sourceNode = $sourceNode;
        $this->destNode = $destNode;
        $this->value = $value;
    }

    /**
     * Returns the value of the edge
     *
     * @return Symfony_Component_DependencyInjection_Compiler_ServiceReferenceGraphNode
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the source node
     *
     * @return Symfony_Component_DependencyInjection_Compiler_ServiceReferenceGraphNode
     */
    public function getSourceNode()
    {
        return $this->sourceNode;
    }

    /**
     * Returns the destination node
     *
     * @return Symfony_Component_DependencyInjection_Compiler_ServiceReferenceGraphNode
     */
    public function getDestNode()
    {
        return $this->destNode;
    }
}
