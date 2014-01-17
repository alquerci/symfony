<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_CssSelector_Tests_Node_ClassNodeTest extends PHPUnit_Framework_TestCase
{
    public function testToXpath()
    {
        // h1.foo
        $element = new Symfony_Component_CssSelector_Node_ElementNode('*', 'h1');
        $class = new Symfony_Component_CssSelector_Node_ClassNode($element, 'foo');

        $this->assertEquals("h1[contains(concat(' ', normalize-space(@class), ' '), ' foo ')]", (string) $class->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');
    }
}
