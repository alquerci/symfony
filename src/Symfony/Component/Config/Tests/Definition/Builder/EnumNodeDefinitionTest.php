<?php

class Symfony_Component_Config_Tests_Definition_Builder_EnumNodeDefinitionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage ->values() must be called with at least two distinct values.
     */
    public function testNoDistinctValues()
    {
        $def = new Symfony_Component_Config_Definition_Builder_EnumNodeDefinition('foo');
        $def->values(array('foo', 'foo'));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage You must call ->values() on enum nodes.
     */
    public function testNoValuesPassed()
    {
        $def = new Symfony_Component_Config_Definition_Builder_EnumNodeDefinition('foo');
        $def->getNode();
    }

    public function testGetNode()
    {
        $def = new Symfony_Component_Config_Definition_Builder_EnumNodeDefinition('foo');
        $def->values(array('foo', 'bar'));

        $node = $def->getNode();
        $this->assertEquals(array('foo', 'bar'), $node->getValues());
    }
}
