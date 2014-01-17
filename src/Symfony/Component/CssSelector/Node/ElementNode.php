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
 * ElementNode represents a "namespace|element" node.
 *
 * This component is a port of the Python lxml library,
 * which is copyright Infrae and distributed under the BSD license.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_CssSelector_Node_ElementNode implements Symfony_Component_CssSelector_Node_NodeInterface
{
    protected $namespace;
    protected $element;

    /**
     * Constructor.
     *
     * @param string $namespace Namespace
     * @param string $element   Element
     */
    public function __construct($namespace, $element)
    {
        $this->namespace = ((is_object($namespace) && method_exists($namespace, '__toString')) ? $namespace->__toString() : $namespace);
        $this->element = ((is_object($element) && method_exists($element, '__toString')) ? $element->__toString() : $element);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return sprintf('%s[%s]', __CLASS__, $this->formatElement());
    }

    /**
     * Formats the element into a string.
     *
     * @return string Element as an XPath string
     */
    public function formatElement()
    {
        if ($this->namespace == '*') {
            return $this->element;
        }

        return sprintf('%s|%s', $this->namespace, $this->element);
    }

    /**
     * {@inheritDoc}
     */
    public function toXpath()
    {
        if ($this->namespace == '*') {
            $el = strtolower($this->element);
        } else {
            // FIXME: Should we lowercase here?
            $el = sprintf('%s:%s', $this->namespace, $this->element);
        }

        return new Symfony_Component_CssSelector_XPathExpr(null, null, $el);
    }
}
