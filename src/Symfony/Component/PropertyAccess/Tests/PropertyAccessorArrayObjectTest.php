<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_PropertyAccess_Tests_PropertyAccessorArrayObjectTest extends Symfony_Component_PropertyAccess_Tests_PropertyAccessorCollectionTest
{
    protected function getCollection(array $array)
    {
        return new ArrayObject($array);
    }
}
