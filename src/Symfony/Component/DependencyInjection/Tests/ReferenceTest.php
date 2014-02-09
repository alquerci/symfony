<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DependencyInjection_Tests_ReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Symfony_Component_DependencyInjection_Reference::__construct
     */
    public function testConstructor()
    {
        $ref = new Symfony_Component_DependencyInjection_Reference('foo');
        $this->assertEquals('foo', (string) $ref->__toString(), '__construct() sets the id of the reference, which is used for the __toString() method');
    }

    public function testCaseInsensitive()
    {
        $ref = new Symfony_Component_DependencyInjection_Reference('FooBar');
        $this->assertEquals('foobar', (string) $ref->__toString(), 'the id is lowercased as the container is case insensitive');
    }
}
