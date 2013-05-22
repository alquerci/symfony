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
 * This iterator just overrides the __call method.
 *
 * @author Alexandre Quercia <alquerci@email.com>
 */
class RecursiveIteratorIterator extends \RecursiveIteratorIterator
{
    public function __call($func, $params)
    {
        $innerIterator = $this->getInnerIterator();   
        $current = $innerIterator->current();

        if (is_object($current) && is_callable(array($current, $func))) {
            return call_user_func_array(array($current, $func), $params);
        }

        return call_user_func_array(array($innerIterator, $func), $params);
    }
}
