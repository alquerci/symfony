<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_CssSelector_Tests_Node_ElementNodeTest extends PHPUnit_Framework_TestCase
{
    public function testToXpath()
    {
        // h1
        $element = new Symfony_Component_CssSelector_Node_ElementNode('*', 'h1');

        $this->assertEquals('h1', (string) $element->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');

        // foo|h1
        $element = new Symfony_Component_CssSelector_Node_ElementNode('foo', 'h1');

        $this->assertEquals('foo:h1', (string) $element->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');
    }
}
