<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_DomCrawler_Tests_Field_FormFieldTest extends Symfony_Component_DomCrawler_Tests_Field_FormFieldTestCase
{
    public function testGetName()
    {
        $node = $this->createNode('input', '', array('type' => 'text', 'name' => 'name', 'value' => 'value'));
        $field = new Symfony_Component_DomCrawler_Field_InputFormField($node);

        $this->assertEquals('name', $field->getName(), '->getName() returns the name of the field');
    }

    public function testGetSetHasValue()
    {
        $node = $this->createNode('input', '', array('type' => 'text', 'name' => 'name', 'value' => 'value'));
        $field = new Symfony_Component_DomCrawler_Field_InputFormField($node);

        $this->assertEquals('value', $field->getValue(), '->getValue() returns the value of the field');

        $field->setValue('foo');
        $this->assertEquals('foo', $field->getValue(), '->setValue() sets the value of the field');

        $this->assertTrue($field->hasValue(), '->hasValue() always returns true');
    }
}
