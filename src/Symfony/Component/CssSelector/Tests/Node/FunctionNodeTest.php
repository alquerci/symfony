<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_CssSelector_Tests_Node_FunctionNodeTest extends PHPUnit_Framework_TestCase
{
    public function testToXpath()
    {
        $element = new Symfony_Component_CssSelector_Node_ElementNode('*', 'h1');

        // h1:contains("foo")
        $function = new Symfony_Component_CssSelector_Node_FunctionNode($element, ':', 'contains', 'foo');
        $this->assertEquals("h1[contains(string(.), 'foo')]", (string) $function->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');

        // h1:nth-child(1)
        $function = new Symfony_Component_CssSelector_Node_FunctionNode($element, ':', 'nth-child', 1);
        $this->assertEquals("*/*[name() = 'h1' and (position() = 1)]", (string) $function->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');

        // h1:nth-child()
        $function = new Symfony_Component_CssSelector_Node_FunctionNode($element, ':', 'nth-child', '');
        $this->assertEquals("h1[false() and position() = 0]", (string) $function->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');

        // h1:nth-child(odd)
        $element2 = new Symfony_Component_CssSelector_Node_ElementNode('*', new Symfony_Component_CssSelector_Token('Symbol', 'odd', -1));
        $function = new Symfony_Component_CssSelector_Node_FunctionNode($element, ':', 'nth-child', $element2);
        $this->assertEquals("*/*[name() = 'h1' and ((position() -1) mod 2 = 0 and position() >= 1)]", (string) $function->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');

        // h1:nth-child(even)
        $element2 = new Symfony_Component_CssSelector_Node_ElementNode('*', new Symfony_Component_CssSelector_Token('Symbol', 'even', -1));
        $function = new Symfony_Component_CssSelector_Node_FunctionNode($element, ':', 'nth-child', $element2);
        $this->assertEquals("*/*[name() = 'h1' and ((position() +0) mod 2 = 0 and position() >= 0)]", (string) $function->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');

        // h1:nth-child(n)
        $element2 = new Symfony_Component_CssSelector_Node_ElementNode('*', new Symfony_Component_CssSelector_Token('Symbol', 'n', -1));
        $function = new Symfony_Component_CssSelector_Node_FunctionNode($element, ':', 'nth-child', $element2);
        $this->assertEquals("*/*[name() = 'h1' and (position() >= 0)]", (string) $function->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');

        // h1:nth-child(3n+1)
        $element2 = new Symfony_Component_CssSelector_Node_ElementNode('*', new Symfony_Component_CssSelector_Token('Symbol', '3n+1', -1));
        $function = new Symfony_Component_CssSelector_Node_FunctionNode($element, ':', 'nth-child', $element2);
        $this->assertEquals("*/*[name() = 'h1' and ((position() -1) mod 3 = 0 and position() >= 1)]", (string) $function->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');

        // h1:nth-child(n+1)
        $element2 = new Symfony_Component_CssSelector_Node_ElementNode('*', new Symfony_Component_CssSelector_Token('Symbol', 'n+1', -1));
        $function = new Symfony_Component_CssSelector_Node_FunctionNode($element, ':', 'nth-child', $element2);
        $this->assertEquals("*/*[name() = 'h1' and (position() >= 1)]", (string) $function->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');

        // h1:nth-child(1)
        $element2 = new Symfony_Component_CssSelector_Node_ElementNode('*', new Symfony_Component_CssSelector_Token('Symbol', '2', -1));
        $function = new Symfony_Component_CssSelector_Node_FunctionNode($element, ':', 'nth-child', $element2);
        $this->assertEquals("*/*[name() = 'h1' and (position() = 2)]", (string) $function->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');

        // h1:nth-child(2n)
        $element2 = new Symfony_Component_CssSelector_Node_ElementNode('*', new Symfony_Component_CssSelector_Token('Symbol', '2n', -1));
        $function = new Symfony_Component_CssSelector_Node_FunctionNode($element, ':', 'nth-child', $element2);
        $this->assertEquals("*/*[name() = 'h1' and ((position() +0) mod 2 = 0 and position() >= 0)]", (string) $function->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');

        // h1:nth-child(-n)
        $element2 = new Symfony_Component_CssSelector_Node_ElementNode('*', new Symfony_Component_CssSelector_Token('Symbol', '-n', -1));
        $function = new Symfony_Component_CssSelector_Node_FunctionNode($element, ':', 'nth-child', $element2);
        $this->assertEquals("*/*[name() = 'h1' and ((position() +0) mod -1 = 0 and position() >= 0)]", (string) $function->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');

        // h1:nth-last-child(2)
        $function = new Symfony_Component_CssSelector_Node_FunctionNode($element, ':', 'nth-last-child', 2);
        $this->assertEquals("*/*[name() = 'h1' and (position() = last() - 2)]", (string) $function->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');

        // h1:nth-of-type(2)
        $function = new Symfony_Component_CssSelector_Node_FunctionNode($element, ':', 'nth-of-type', 2);
        $this->assertEquals("*/h1[position() = 2]", (string) $function->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');

        // h1:nth-last-of-type(2)
        $function = new Symfony_Component_CssSelector_Node_FunctionNode($element, ':', 'nth-last-of-type', 2);
        $this->assertEquals("*/h1[position() = last() - 2]", (string) $function->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');

        /*
        // h1:not(p)
        $element2 = new Symfony_Component_CssSelector_Node_ElementNode('*', 'p');
        $function = new Symfony_Component_CssSelector_Node_FunctionNode($element, ':', 'not', $element2);

        $this->assertEquals("h1[not()]", (string) $function->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');
        */
    }
}
