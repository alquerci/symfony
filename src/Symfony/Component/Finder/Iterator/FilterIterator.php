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
 * This iterator just overrides the rewind method in order to correct a PHP bug.
 *
 * @see https://bugs.php.net/bug.php?id=49104
 *
 * @author Alex Bogomazov
 */
abstract class Symfony_Component_Finder_Iterator_FilterIterator extends FilterIterator
{
    /**
     * @var Iterator
     */
    private $it;

    public function __construct(Iterator $iterator)
    {
        $this->it = $iterator;
    }

    public function __call($func, $params)
    {
        return call_user_func_array(array($this->it, $func), $params);
    }

    protected function __clone()
    {

    }

    public function current()
    {
        return $this->it->current();
    }

    public function key()
    {
        return $this->it->key();
    }

    public function valid()
    {
        return $this->it->valid();
    }

    protected function fetch()
    {
        while ($this->it->valid()) {
            if ($this->accept()) {
                return;
            }
            $this->it->next();
        }
    }

    public function getInnerIterator()
    {
        return $this->it;
    }

    public function next()
    {
        $this->it->next();
        $this->fetch();
    }

    /**
     * This is a workaround for the problem with \FilterIterator leaving inner \FilesystemIterator in wrong state after
     * rewind in some cases.
     *
     * @see FilterIterator::rewind()
     */
    public function rewind()
    {
        $iterator = $this;
        while ($iterator instanceof OuterIterator) {
            if ($iterator->getInnerIterator() instanceof FilesystemIterator) {
                $iterator->getInnerIterator()->next();
                $iterator->getInnerIterator()->rewind();
            }
            $iterator = $iterator->getInnerIterator();
        }

        $this->it->rewind();
        $this->fetch();
    }
}
