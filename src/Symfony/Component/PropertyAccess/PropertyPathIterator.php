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
 * Traverses a property path and provides additional methods to find out
 * information about the current element
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_PropertyAccess_PropertyPathIterator extends ArrayIterator implements Symfony_Component_PropertyAccess_PropertyPathIteratorInterface
{
    /**
     * The traversed property path
     * @var Symfony_Component_PropertyAccess_PropertyPathInterface
     */
    protected $path;

    /**
     * Constructor.
     *
     * @param Symfony_Component_PropertyAccess_PropertyPathInterface $path The property path to traverse
     */
    public function __construct(Symfony_Component_PropertyAccess_PropertyPathInterface $path)
    {
        parent::__construct($path->getElements());

        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function isIndex()
    {
        return $this->path->isIndex($this->key());
    }

    /**
     * {@inheritdoc}
     */
    public function isProperty()
    {
        return $this->path->isProperty($this->key());
    }
}
