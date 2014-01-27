<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Config_Tests_Definition_PrototypedArrayNodeTest extends PHPUnit_Framework_TestCase
{
    public function testGetDefaultValueReturnsAnEmptyArrayForPrototypes()
    {
        $node = new Symfony_Component_Config_Definition_PrototypedArrayNode('root');
        $prototype = new Symfony_Component_Config_Definition_ArrayNode(null, $node);
        $node->setPrototype($prototype);
        $this->assertEquals(0, count($node->getDefaultValue()));
    }

    public function testGetDefaultValueReturnsDefaultValueForPrototypes()
    {
        $node = new Symfony_Component_Config_Definition_PrototypedArrayNode('root');
        $prototype = new Symfony_Component_Config_Definition_ArrayNode(null, $node);
        $node->setPrototype($prototype);
        $node->setDefaultValue(array('test'));
        $this->assertEquals(array('test'), $node->getDefaultValue());
    }

    // a remapped key (e.g. "mapping" -> "mappings") should be unset after being used
    public function testRemappedKeysAreUnset()
    {
        $node = new Symfony_Component_Config_Definition_ArrayNode('root');
        $mappingsNode = new Symfony_Component_Config_Definition_PrototypedArrayNode('mappings');
        $node->addChild($mappingsNode);

        // each item under mappings is just a scalar
        $prototype = new Symfony_Component_Config_Definition_ScalarNode(null, $mappingsNode);
        $mappingsNode->setPrototype($prototype);

        $remappings = array();
        $remappings[] = array('mapping', 'mappings');
        $node->setXmlRemappings($remappings);

        $normalized = $node->normalize(array('mapping' => array('foo', 'bar')));
        $this->assertEquals(array('mappings' => array('foo', 'bar')), $normalized);
    }

    /**
     * Tests that when a key attribute is mapped, that key is removed from the array:
     *
     *     <things>
     *         <option id="option1" value="foo">
     *         <option id="option2" value="bar">
     *     </things>
     *
     * The above should finally be mapped to an array that looks like this
     * (because "id" is the key attribute).
     *
     *     array(
     *         'things' => array(
     *             'option1' => 'foo',
     *             'option2' => 'bar',
     *         )
     *     )
     */
    public function testMappedAttributeKeyIsRemoved()
    {
        $node = new Symfony_Component_Config_Definition_PrototypedArrayNode('root');
        $node->setKeyAttribute('id', true);

        // each item under the root is an array, with one scalar item
        $prototype = new Symfony_Component_Config_Definition_ArrayNode(null, $node);
        $prototype->addChild(new Symfony_Component_Config_Definition_ScalarNode('foo'));
        $node->setPrototype($prototype);

        $children = array();
        $children[] = array('id' => 'item_name', 'foo' => 'bar');
        $normalized = $node->normalize($children);

        $expected = array();
        $expected['item_name'] = array('foo' => 'bar');
        $this->assertEquals($expected, $normalized);
    }

    /**
     * Tests the opposite of the testMappedAttributeKeyIsRemoved because
     * the removal can be toggled with an option.
     */
    public function testMappedAttributeKeyNotRemoved()
    {
        $node = new Symfony_Component_Config_Definition_PrototypedArrayNode('root');
        $node->setKeyAttribute('id', false);

        // each item under the root is an array, with two scalar items
        $prototype = new Symfony_Component_Config_Definition_ArrayNode(null, $node);
        $prototype->addChild(new Symfony_Component_Config_Definition_ScalarNode('foo'));
        $prototype->addChild(new Symfony_Component_Config_Definition_ScalarNode('id')); // the key attribute will remain
        $node->setPrototype($prototype);

        $children = array();
        $children[] = array('id' => 'item_name', 'foo' => 'bar');
        $normalized = $node->normalize($children);

        $expected = array();
        $expected['item_name'] = array('id' => 'item_name', 'foo' => 'bar');
        $this->assertEquals($expected, $normalized);
    }

    public function testAddDefaultChildren()
    {
        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setAddChildrenIfNoneSet();
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals(array(array('foo' => 'bar')), $node->getDefaultValue());

        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setKeyAttribute('foobar');
        $node->setAddChildrenIfNoneSet();
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals(array('defaults' => array('foo' => 'bar')), $node->getDefaultValue());

        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setKeyAttribute('foobar');
        $node->setAddChildrenIfNoneSet('defaultkey');
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals(array('defaultkey' => array('foo' => 'bar')), $node->getDefaultValue());

        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setKeyAttribute('foobar');
        $node->setAddChildrenIfNoneSet(array('defaultkey'));
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals(array('defaultkey' => array('foo' => 'bar')), $node->getDefaultValue());

        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setKeyAttribute('foobar');
        $node->setAddChildrenIfNoneSet(array('dk1', 'dk2'));
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals(array('dk1' => array('foo' => 'bar'), 'dk2' => array('foo' => 'bar')), $node->getDefaultValue());

        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setAddChildrenIfNoneSet(array(5, 6));
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals(array(0 => array('foo' => 'bar'), 1 => array('foo' => 'bar')), $node->getDefaultValue());

        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setAddChildrenIfNoneSet(2);
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals(array(array('foo' => 'bar'), array('foo' => 'bar')), $node->getDefaultValue());
    }

    public function testDefaultChildrenWinsOverDefaultValue()
    {
        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setAddChildrenIfNoneSet();
        $node->setDefaultValue(array('bar' => 'foo'));
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals(array(array('foo' => 'bar')), $node->getDefaultValue());
    }

    protected function getPrototypeNodeWithDefaultChildren()
    {
        $node = new Symfony_Component_Config_Definition_PrototypedArrayNode('root');
        $prototype = new Symfony_Component_Config_Definition_ArrayNode(null, $node);
        $child = new Symfony_Component_Config_Definition_ScalarNode('foo');
        $child->setDefaultValue('bar');
        $prototype->addChild($child);
        $prototype->setAddIfNotSet(true);
        $node->setPrototype($prototype);

        return $node;
    }
}
