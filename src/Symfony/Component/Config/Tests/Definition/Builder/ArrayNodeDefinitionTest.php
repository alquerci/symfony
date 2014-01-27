<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Config_Tests_Definition_Builder_ArrayNodeDefinitionTest extends PHPUnit_Framework_TestCase
{
    public function testAppendingSomeNode()
    {
        $parent = new Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition('root');
        $child = new Symfony_Component_Config_Definition_Builder_ScalarNodeDefinition('child');

        $parent
            ->children()
                ->scalarNode('foo')->end()
                ->scalarNode('bar')->end()
            ->end()
            ->append($child);

        $this->assertEquals(3, count($this->getField($parent, 'children')));
        $this->assertTrue(in_array($child, $this->getField($parent, 'children')));
    }

    /**
     * @expectedException Symfony_Component_Config_Definition_Exception_InvalidDefinitionException
     * @dataProvider providePrototypeNodeSpecificCalls
     */
    public function testPrototypeNodeSpecificOption($method, $args)
    {
        $node = new Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition('root');

        call_user_func_array(array($node, $method), $args);

        $node->getNode();
    }

    public function providePrototypeNodeSpecificCalls()
    {
        return array(
            array('defaultValue', array(array())),
            array('addDefaultChildrenIfNoneSet', array()),
            array('requiresAtLeastOneElement', array()),
            array('useAttributeAsKey', array('foo'))
        );
    }

    /**
     * @expectedException Symfony_Component_Config_Definition_Exception_InvalidDefinitionException
     */
    public function testConcreteNodeSpecificOption()
    {
        $node = new Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition('root');
        $node
            ->addDefaultsIfNotSet()
            ->prototype('array')
        ;
        $node->getNode();
    }

    /**
     * @expectedException Symfony_Component_Config_Definition_Exception_InvalidDefinitionException
     */
    public function testPrototypeNodesCantHaveADefaultValueWhenUsingDefaultChildren()
    {
        $node = new Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition('root');
        $node
            ->defaultValue(array())
            ->addDefaultChildrenIfNoneSet('foo')
            ->prototype('array')
        ;
        $node->getNode();
    }

    public function testPrototypedArrayNodeDefaultWhenUsingDefaultChildren()
    {
        $node = new Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition('root');
        $node
            ->addDefaultChildrenIfNoneSet()
            ->prototype('array')
        ;
        $tree = $node->getNode();
        $this->assertEquals(array(array()), $tree->getDefaultValue());
    }

    /**
     * @dataProvider providePrototypedArrayNodeDefaults
     */
    public function testPrototypedArrayNodeDefault($args, $shouldThrowWhenUsingAttrAsKey, $shouldThrowWhenNotUsingAttrAsKey, $defaults)
    {
        $node = new Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition('root');
        $node
            ->addDefaultChildrenIfNoneSet($args)
            ->prototype('array')
        ;

        try {
            $tree = $node->getNode();
            $this->assertFalse($shouldThrowWhenNotUsingAttrAsKey);
            $this->assertEquals($defaults, $tree->getDefaultValue());
        } catch (Symfony_Component_Config_Definition_Exception_InvalidDefinitionException $e) {
            $this->assertTrue($shouldThrowWhenNotUsingAttrAsKey);
        }

        $node = new Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition('root');
        $node
            ->useAttributeAsKey('attr')
            ->addDefaultChildrenIfNoneSet($args)
            ->prototype('array')
        ;

        try {
            $tree = $node->getNode();
            $this->assertFalse($shouldThrowWhenUsingAttrAsKey);
            $this->assertEquals($defaults, $tree->getDefaultValue());
        } catch (Symfony_Component_Config_Definition_Exception_InvalidDefinitionException $e) {
            $this->assertTrue($shouldThrowWhenUsingAttrAsKey);
        }
    }

    public function providePrototypedArrayNodeDefaults()
    {
        return array(
            array(null, true, false, array(array())),
            array(2, true, false, array(array(), array())),
            array('2', false, true, array('2' => array())),
            array('foo', false, true, array('foo' => array())),
            array(array('foo'), false, true, array('foo' => array())),
            array(array('foo', 'bar'), false, true, array('foo' => array(), 'bar' => array())),
        );
    }

    public function testNestedPrototypedArrayNodes()
    {
        $node = new Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition('root');
        $node
            ->addDefaultChildrenIfNoneSet()
            ->prototype('array')
                  ->prototype('array')
        ;
        $node->getNode();
    }

    public function testEnabledNodeDefaults()
    {
        $node = new Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition('root');
        $node
            ->canBeEnabled()
            ->children()
                ->scalarNode('foo')->defaultValue('bar')->end()
        ;

        $this->assertEquals(array('enabled' => false, 'foo' => 'bar'), $node->getNode()->getDefaultValue());
    }

    /**
     * @dataProvider getEnableableNodeFixtures
     */
    public function testTrueEnableEnabledNode($expected, $config, $message)
    {
        $processor = new Symfony_Component_Config_Definition_Processor();
        $node = new Symfony_Component_Config_Definition_Builder_ArrayNodeDefinition('root');
        $node
            ->canBeEnabled()
            ->children()
                ->scalarNode('foo')->defaultValue('bar')->end()
        ;

        $this->assertEquals(
            $expected,
            $processor->process($node->getNode(), $config),
            $message
        );
    }

    public function getEnableableNodeFixtures()
    {
        return array(
            array(array('enabled' => true, 'foo' => 'bar'), array(true), 'true enables an enableable node'),
            array(array('enabled' => true, 'foo' => 'bar'), array(null), 'null enables an enableable node'),
            array(array('enabled' => true, 'foo' => 'bar'), array(array('enabled' => true)), 'An enableable node can be enabled'),
            array(array('enabled' => true, 'foo' => 'baz'), array(array('foo' => 'baz')), 'any configuration enables an enableable node'),
            array(array('enabled' => false, 'foo' => 'baz'), array(array('foo' => 'baz', 'enabled' => false)), 'An enableable node can be disabled'),
            array(array('enabled' => false, 'foo' => 'bar'), array(false), 'false disables an enableable node'),
        );
    }

    protected function getField($object, $field)
    {
        return $this->readAttribute($object, $field);
    }
}
