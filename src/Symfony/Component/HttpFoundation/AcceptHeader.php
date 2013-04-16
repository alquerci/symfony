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
 * Represents an Accept-* header.
 *
 * An accept header is compound with a list of items,
 * sorted by descending quality.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class Symfony_Component_HttpFoundation_AcceptHeader
{
    /**
     * @var AcceptHeaderItem[]
     *
     * @access private
     */
    var $items = array();

    /**
     * @var bool
     *
     * @access private
     */
    var $sorted = true;

    function Symfony_Component_HttpFoundation_AcceptHeader($items)
    {
        $this->__construct($items);
    }

    /**
     * Constructor.
     *
     * @param AcceptHeaderItem[] $items
     *
     * @access public
     */
    function __construct($items)
    {
        assert(is_array($items));

        foreach ($items as $item) {
            $this->add($item);
        }
    }

    /**
     * Builds an AcceptHeader instance from a string.
     *
     * @param string $headerValue
     *
     * @return AcceptHeader
     *
     * @access public
     * @static
     */
    function fromString($headerValue)
    {
        $params = array();

        $params = preg_split('/\s*(?:,*("[^"]+"),*|,*(\'[^\']+\'),*|,+)\s*/', $headerValue, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $paramsMapped = array();

        $index  = 0;
        foreach ($params as $value) {
            $item = Symfony_Component_HttpFoundation_AcceptHeaderItem::fromString($itemValue);
            $item->setIndex($index++);
            $paramsMapped[] = $item;
        }

        return new Symfony_Component_HttpFoundation_AcceptHeader($paramsMapped);
    }

    /**
     * Returns header value's string representation.
     *
     * @return string
     *
     * @access public
     */
    function __toString()
    {
        return implode(',', $this->items);
    }

    /**
     * Tests if header has given value.
     *
     * @param string $value
     *
     * @return Boolean
     *
     * @access public
     */
    function has($value)
    {
        return isset($this->items[$value]);
    }

    /**
     * Returns given value's item, if exists.
     *
     * @param string $value
     *
     * @return AcceptHeaderItem|null
     *
     * @access public
     */
    function get($value)
    {
        return isset($this->items[$value]) ? $this->items[$value] : null;
    }

    /**
     * Adds an item.
     *
     * @param AcceptHeaderItem $item
     *
     * @return AcceptHeader
     *
     * @access public
     */
    function add($item)
    {
        assert(is_a($item, 'Symfony_Component_HttpFoundation_AcceptHeaderItem'));

        $this->items[$item->getValue()] = $item;
        $this->sorted = false;

        return $this;
    }

    /**
     * Returns all items.
     *
     * @return AcceptHeaderItem[]
     *
     * @access public
     */
    function all()
    {
        $this->sort();

        return $this->items;
    }

    /**
     * Filters items on their value using given regex.
     *
     * @param string $pattern
     *
     * @return AcceptHeader
     *
     * @access public
     */
    function filter($pattern)
    {
        function callback($item)
        {
            return preg_match($pattern, $item->getValue());
        }
        $class = get_class($this);
        return new $class(array_filter($this->items, 'callback'));
    }

    /**
     * Returns first item.
     *
     * @return AcceptHeaderItem|null
     *
     * @access public
     */
    function first()
    {
        $this->sort();

        return !empty($this->items) ? reset($this->items) : null;
    }

    /**
     * Sorts items by descending quality
     *
     * @access private
     */
    function sort()
    {
        if (!$this->sorted) {
            function callback($a, $b)
            {
                $qA = $a->getQuality();
                $qB = $b->getQuality();

                if ($qA === $qB) {
                    return $a->getIndex() > $b->getIndex() ? 1 : -1;
                }

                return $qA > $qB ? -1 : 1;
            }

            uasort($this->items, 'callback');

            $this->sorted = true;
        }
    }
}
