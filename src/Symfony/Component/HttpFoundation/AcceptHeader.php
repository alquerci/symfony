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
     * @var Symfony_Component_HttpFoundation_AcceptHeaderItem[]
     */
    private $items = array();

    /**
     * @var bool
     */
    private $sorted = true;

    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpFoundation_AcceptHeaderItem[] $items
     */
    public function __construct(array $items)
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    /**
     * Builds an AcceptHeader instance from a string.
     *
     * @param string $headerValue
     *
     * @return Symfony_Component_HttpFoundation_AcceptHeader
     */
    public static function fromString($headerValue)
    {
        self::$_fromStringIndex = 0;

        return new self(array_map(array('Symfony_Component_HttpFoundation_AcceptHeader', '_fromStringCB'), preg_split('/\s*(?:,*("[^"]+"),*|,*(\'[^\']+\'),*|,+)\s*/', $headerValue, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE)));
    }

    private static $_fromStringIndex = 0;

    public static function _fromStringCB($itemValue)
    {
        $item = Symfony_Component_HttpFoundation_AcceptHeaderItem::fromString($itemValue);
        $item->setIndex(self::$_fromStringIndex++);

        return $item;
    }

    /**
     * Returns header value's string representation.
     *
     * @return string
     */
    public function __toString()
    {
        $strItems = array();
        foreach ($this->items as $item) {
            $strItems[] = $item->__toString();
        }

        return implode(',', $strItems);
    }

    /**
     * Tests if header has given value.
     *
     * @param string $value
     *
     * @return Boolean
     */
    public function has($value)
    {
        return isset($this->items[$value]);
    }

    /**
     * Returns given value's item, if exists.
     *
     * @param string $value
     *
     * @return Symfony_Component_HttpFoundation_AcceptHeaderItem|null
     */
    public function get($value)
    {
        return isset($this->items[$value]) ? $this->items[$value] : null;
    }

    /**
     * Adds an item.
     *
     * @param Symfony_Component_HttpFoundation_AcceptHeaderItem $item
     *
     * @return Symfony_Component_HttpFoundation_AcceptHeader
     */
    public function add(Symfony_Component_HttpFoundation_AcceptHeaderItem $item)
    {
        $this->items[$item->getValue()] = $item;
        $this->sorted = false;

        return $this;
    }

    /**
     * Returns all items.
     *
     * @return Symfony_Component_HttpFoundation_AcceptHeaderItem[]
     */
    public function all()
    {
        $this->sort();

        return $this->items;
    }

    /**
     * Filters items on their value using given regex.
     *
     * @param string $pattern
     *
     * @return Symfony_Component_HttpFoundation_AcceptHeader
     */
    public function filter($pattern)
    {
        $this->_filterPattern = $pattern;

        return new self(array_filter($this->items, array($this, '_filterCB')));
    }

    private $_filterPattern = null;

    public function _filterCB(Symfony_Component_HttpFoundation_AcceptHeaderItem $item)
    {
        return preg_match($this->_filterPattern, $item->getValue());
    }

    /**
     * Returns first item.
     *
     * @return Symfony_Component_HttpFoundation_AcceptHeaderItem|null
     */
    public function first()
    {
        $this->sort();

        return !empty($this->items) ? reset($this->items) : null;
    }

    /**
     * Sorts items by descending quality
     */
    private function sort()
    {
        if (!$this->sorted) {
            uasort($this->items, create_function('$a, $b', '
                $qA = $a->getQuality();
                $qB = $b->getQuality();

                if ($qA === $qB) {
                    return $a->getIndex() > $b->getIndex() ? 1 : -1;
                }

                return $qA > $qB ? -1 : 1;
            '));

            $this->sorted = true;
        }
    }
}
