<?php

/*
 * (c) Alexandre Quercia <alquerci@email.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Alexandre Quercia <alquerci@email.com>
 */
class Symfony_Component_Finder_Iterator_RecursiveIteratorIterator extends RecursiveIteratorIterator
{
    public function __call($func, $params)
    {
        return call_user_func_array(array($this->getInnerIterator(), $func), $params);
    }
}
