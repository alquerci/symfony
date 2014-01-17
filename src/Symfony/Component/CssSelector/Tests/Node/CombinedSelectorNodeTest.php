<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_CssSelector_Tests_Node_CombinedSelectorNodeTest extends PHPUnit_Framework_TestCase
{
    public function testToXpath()
    {
        $combinators = array(
            ' ' => "h1/descendant::p",
            '>' => "h1/p",
            '+' => "h1/following-sibling::*[name() = 'p' and (position() = 1)]",
            '~' => "h1/following-sibling::p",
        );

        // h1 ?? p
        $element1 = new Symfony_Component_CssSelector_Node_ElementNode('*', 'h1');
        $element2 = new Symfony_Component_CssSelector_Node_ElementNode('*', 'p');
        foreach ($combinators as $combinator => $xpath) {
            $combinator = new Symfony_Component_CssSelector_Node_CombinedSelectorNode($element1, $combinator, $element2);
            $this->assertEquals($xpath, (string) $combinator->toXpath()->__toString(), '->toXpath() returns the xpath representation of the node');
        }
    }
}
