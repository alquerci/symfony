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
 * OrNode represents a "Or" node.
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_CssSelector_Node_OrNode implements Symfony_Component_CssSelector_Node_NodeInterface
{
    /**
     * @var Symfony_Component_CssSelector_Node_NodeInterface[]
     */
    protected $items;

    /**
     * Constructor.
     *
     * @param Symfony_Component_CssSelector_Node_NodeInterface[] $items An array of NodeInterface objects
     */
    public function __construct($items)
    {
        $this->items = $items;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return sprintf('%s(%s)', __CLASS__, $this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function toXpath()
    {
        $paths = array();
        foreach ($this->items as $item) {
            $paths[] = $item->toXpath();
        }

        return new Symfony_Component_CssSelector_XPathExprOr($paths);
    }
}
