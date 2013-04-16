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
 * Represents an Accept-* header item.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class Symfony_Component_HttpFoundation_AcceptHeaderItem
{
    /**
     * @var string
     *
     * @access private
     */
    var $value;

    /**
     * @var float
     *
     * @access private
     */
    var $quality = 1.0;

    /**
     * @var int
     *
     * @access private
     */
    var $index = 0;

    /**
     * @var array
     *
     * @access private
     */
    var $attributes = array();

    /**
     * Constructor.
     *
     * @param string $value
     * @param array  $attributes
     *
     * @access public
     */
    function Symfony_Component_HttpFoundation_AcceptHeaderItem($value, $attributes = array())
    {
        assert(is_array($attributes));

        $this->value = $value;
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    /**
     * Builds an AcceptHeaderInstance instance from a string.
     *
     * @param string $itemValue
     *
     * @return AcceptHeaderItem
     *
     * @access public
     * @static
     */
    function fromString($itemValue)
    {
        $bits = preg_split('/\s*(?:;*("[^"]+");*|;*(\'[^\']+\');*|;+)\s*/', $itemValue, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $value = array_shift($bits);
        $attributes = array();

        $lastNullAttribute = null;
        foreach ($bits as $bit) {
            if (($start = substr($bit, 0, 1)) === ($end = substr($bit, -1)) && ($start === '"' || $start === '\'')) {
                $attributes[$lastNullAttribute] = substr($bit, 1, -1);
            } elseif ('=' === $end) {
                $lastNullAttribute = $bit = substr($bit, 0, -1);
                $attributes[$bit] = null;
            } else {
                $parts = explode('=', $bit);
                $attributes[$parts[0]] = isset($parts[1]) && strlen($parts[1]) > 0 ? $parts[1] : '';
            }
        }

        return new Symfony_Component_HttpFoundation_AcceptHeaderItem(($start = substr($value, 0, 1)) === ($end = substr($value, -1)) && ($start === '"' || $start === '\'') ? substr($value, 1, -1) : $value, $attributes);
    }

    /**
     * Returns header  value's string representation.
     *
     * @return string
     *
     * @access public
     */
    function __toString()
    {
        function callback($name, $value)
        {
            return sprintf(preg_match('/[,;=]/', $value) ? '%s="%s"' : '%s=%s', $name, $value);
        }

        $string = $this->value.($this->quality < 1 ? ';q='.$this->quality : '');
        if (count($this->attributes) > 0) {
            $string .= ';'.implode(';', array_map('callback', array_keys($this->attributes), $this->attributes));
        }

        return $string;
    }

    /**
     * Set the item value.
     *
     * @param string $value
     *
     * @return AcceptHeaderItem
     */
    function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Returns the item value.
     *
     * @return string
     */
    function getValue()
    {
        return $this->value;
    }

    /**
     * Set the item quality.
     *
     * @param float $quality
     *
     * @return AcceptHeaderItem
     *
     * @access public
     */
    function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * Returns the item quality.
     *
     * @return float
     *
     * @access public
     */
    function getQuality()
    {
        return $this->quality;
    }

    /**
     * Set the item index.
     *
     * @param int $index
     *
     * @return AcceptHeaderItem
     *
     * @access public
     */
    function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Returns the item index.
     *
     * @return int
     *
     * @access public
     */
    function getIndex()
    {
        return $this->index;
    }

    /**
     * Tests if an attribute exists.
     *
     * @param string $name
     *
     * @return Boolean
     *
     * @access public
     */
    function hasAttribute($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Returns an attribute by its name.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     *
     * @access public
     */
    function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * Returns all attributes.
     *
     * @return array
     *
     * @access public
     */
    function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set an attribute.
     *
     * @param string $name
     * @param string $value
     *
     * @return AcceptHeaderItem
     *
     * @access public
     */
    function setAttribute($name, $value)
    {
        if ('q' === $name) {
            $this->quality = (float) $value;
        } else {
            $this->attributes[$name] = (string) $value;
        }

        return $this;
    }
}
