<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_PropertyAccess_Tests_Fixtures_Magician
{
    private $foobar;

    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    public function __get($property)
    {
        return isset($this->$property) ? $this->$property : null;
    }
}
