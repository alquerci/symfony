<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Finder\Iterator;

/**
 * This iterator just overrides the rewind method in order to correct a PHP bug.
 *
 * @see https://bugs.php.net/bug.php?id=49104
 *
 * @author Alex Bogomazov
 */
abstract class FilterIterator extends \FilterIterator
{
    /**
     * Aggregate the inner iterator.
     *
     * @param string $func   Name of method to invoke
     * @param string $params Array of parameters to pass to method
     *
     * @return mixed
     */
    public function __call($func, $params)
    {
        $innerIterator = $this->getInnerIterator();
        $current = $innerIterator->current();

        if (is_object($current) && is_callable(array($current, $func))) {
            return call_user_func_array(array($current, $func), $params);
        }

        return call_user_func_array(array($innerIterator, $func), $params);
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
        while ($iterator instanceof \OuterIterator) {
            if ($iterator->getInnerIterator() instanceof \FilesystemIterator) {
                $iterator->getInnerIterator()->next();
                $iterator->getInnerIterator()->rewind();
            }
            $iterator = $iterator->getInnerIterator();
        }

        parent::rewind();
    }
}
