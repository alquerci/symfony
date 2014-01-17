<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_CssSelector_Tests_Node_AttribNodeTest extends PHPUnit_Framework_TestCase
{
    public function testToXpath()
    {
        $element = new Symfony_Component_CssSelector_Node_ElementNode('*', 'h1');

        $operators = array(
            '^=' => "h1[starts-with(@class, 'foo')]",
            '$=' => "h1[substring(@class, string-length(@class)-2) = 'foo']",
            '*=' => "h1[contains(@class, 'foo')]",
            '='  => "h1[@class = 'foo']",
            '~=' => "h1[contains(concat(' ', normalize-space(@class), ' '), ' foo ')]",
            '|=' => "h1[@class = 'foo' or starts-with(@class, 'foo-')]",
            '!=' => "h1[not(@class) or @class != 'foo']",
        );

        // h1[class??foo]
        foreach ($operators as $op => $xpath) {
            $attrib = new Symfony_Component_CssSelector_Node_AttribNode($element, '*', 'class', $op, 'foo');
            $this->assertEquals($xpath, (string) $attrib->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');
        }

        // h1[class]
        $attrib = new Symfony_Component_CssSelector_Node_AttribNode($element, '*', 'class', 'exists', 'foo');
        $this->assertEquals('h1[@class]', (string) $attrib->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');
    }
}
