<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Templating_Tests_Storage_StorageTest extends PHPUnit_Framework_TestCase
{
    public function testMagicToString()
    {
        $storage = new Symfony_Component_Templating_Tests_Storage_TestStorage('foo');
        $this->assertEquals('foo', (string) $storage->__toString(), '__toString() returns the template name');
    }
}

class Symfony_Component_Templating_Tests_Storage_TestStorage extends Symfony_Component_Templating_Storage_Storage
{
    public function getContent()
    {
    }
}
