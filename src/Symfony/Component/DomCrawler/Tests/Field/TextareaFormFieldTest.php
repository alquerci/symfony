<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DomCrawler_Tests_Field_TextareaFormFieldTest extends Symfony_Component_DomCrawler_Tests_Field_FormFieldTestCase
{
    public function testInitialize()
    {
        $node = $this->createNode('textarea', 'foo bar');
        $field = new Symfony_Component_DomCrawler_Field_TextareaFormField($node);

        $this->assertEquals('foo bar', $field->getValue(), '->initialize() sets the value of the field to the textarea node value');

        $node = $this->createNode('input', '');
        try {
            $field = new Symfony_Component_DomCrawler_Field_TextareaFormField($node);
            $this->fail('->initialize() throws a LogicException if the node is not a textarea');
        } catch (LogicException $e) {
            $this->assertTrue(true, '->initialize() throws a LogicException if the node is not a textarea');
        }
    }
}
