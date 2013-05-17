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
 * MultiplePcreFilterIterator filters files using patterns (regexps, globs or strings).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Symfony_Component_Finder_Iterator_MultiplePcreFilterIterator extends Symfony_Component_Finder_Iterator_FilterIterator
{
    protected $matchRegexps;
    protected $noMatchRegexps;

    /**
     * Constructor.
     *
     * @param Iterator $iterator        The Iterator to filter
     * @param array     $matchPatterns   An array of patterns that need to match
     * @param array     $noMatchPatterns An array of patterns that need to not match
     */
    public function __construct(Iterator $iterator, array $matchPatterns, array $noMatchPatterns)
    {
        $this->matchRegexps = array();
        foreach ($matchPatterns as $pattern) {
            $this->matchRegexps[] = $this->toRegex($pattern);
        }

        $this->noMatchRegexps = array();
        foreach ($noMatchPatterns as $pattern) {
            $this->noMatchRegexps[] = $this->toRegex($pattern);
        }

        parent::__construct($iterator);
    }

    /**
     * Checks whether the string is a regex.
     *
     * @param string $str
     *
     * @return Boolean Whether the given string is a regex
     */
    protected function isRegex($str)
    {
        return Symfony_Component_Finder_Expression_Expression::create($str)->isRegex();
    }

    /**
     * Converts string into regexp.
     *
     * @param string $str Pattern
     *
     * @return string regexp corresponding to a given string
     */
    abstract protected function toRegex($str);
}
