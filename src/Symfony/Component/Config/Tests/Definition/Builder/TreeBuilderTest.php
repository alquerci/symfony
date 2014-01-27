<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__).'/../../Fixtures/Builder/NodeBuilder.php';
require dirname(__FILE__).'/../../Fixtures/Builder/BarNodeDefinition.php';
require dirname(__FILE__).'/../../Fixtures/Builder/VariableNodeDefinition.php';

class Symfony_Component_Config_Tests_Definition_Builder_TreeBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testUsingACustomNodeBuilder()
    {
        $builder = new Symfony_Component_Config_Definition_Builder_TreeBuilder();
        $root = $builder->root('custom', 'array', new Symfony_Component_Config_Tests_Fixtures_Builder_NodeBuilder());

        $nodeBuilder = $root->children();

        $this->assertEquals(get_class($nodeBuilder), 'Symfony_Component_Config_Tests_Fixtures_Builder_NodeBuilder');

        $nodeBuilder = $nodeBuilder->arrayNode('deeper')->children();

        $this->assertEquals(get_class($nodeBuilder), 'Symfony_Component_Config_Tests_Fixtures_Builder_NodeBuilder');
    }

    public function testOverrideABuiltInNodeType()
    {
        $builder = new Symfony_Component_Config_Definition_Builder_TreeBuilder();
        $root = $builder->root('override', 'array', new Symfony_Component_Config_Tests_Fixtures_Builder_NodeBuilder());

        $definition = $root->children()->variableNode('variable');

        $this->assertEquals(get_class($definition), 'Symfony_Component_Config_Tests_Fixtures_Builder_VariableNodeDefinition');
    }

    public function testAddANodeType()
    {
        $builder = new Symfony_Component_Config_Definition_Builder_TreeBuilder();
        $root = $builder->root('override', 'array', new Symfony_Component_Config_Tests_Fixtures_Builder_NodeBuilder());

        $definition = $root->children()->barNode('variable');

        $this->assertEquals(get_class($definition), 'Symfony_Component_Config_Tests_Fixtures_Builder_BarNodeDefinition');
    }

    public function testCreateABuiltInNodeTypeWithACustomNodeBuilder()
    {
        $builder = new Symfony_Component_Config_Definition_Builder_TreeBuilder();
        $root = $builder->root('builtin', 'array', new Symfony_Component_Config_Tests_Fixtures_Builder_NodeBuilder());

        $definition = $root->children()->booleanNode('boolean');

        $this->assertEquals(get_class($definition), 'Symfony_Component_Config_Definition_Builder_BooleanNodeDefinition');
    }

    public function testPrototypedArrayNodeUseTheCustomNodeBuilder()
    {
        $builder = new Symfony_Component_Config_Definition_Builder_TreeBuilder();
        $root = $builder->root('override', 'array', new Symfony_Component_Config_Tests_Fixtures_Builder_NodeBuilder());

        $root->prototype('bar')->end();
    }

    public function testAnExtendedNodeBuilderGetsPropagatedToTheChildren()
    {
        $builder = new Symfony_Component_Config_Definition_Builder_TreeBuilder();

        $builder->root('propagation')
            ->children()
                ->setNodeClass('extended', 'Symfony_Component_Config_Tests_Fixtures_Builder_VariableNodeDefinition')
                ->node('foo', 'extended')->end()
                ->arrayNode('child')
                    ->children()
                        ->node('foo', 'extended')
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    public function testDefinitionInfoGetsTransferredToNode()
    {
        $builder = new Symfony_Component_Config_Definition_Builder_TreeBuilder();

        $builder->root('test')->info('root info')
            ->children()
                ->node('child', 'variable')->info('child info')->defaultValue('default')
            ->end()
        ->end();

        $tree = $builder->buildTree();
        $children = $tree->getChildren();

        $this->assertEquals('root info', $tree->getInfo());
        $this->assertEquals('child info', $children['child']->getInfo());
    }

    public function testDefinitionExampleGetsTransferredToNode()
    {
        $builder = new Symfony_Component_Config_Definition_Builder_TreeBuilder();

        $builder->root('test')
            ->example(array('key' => 'value'))
            ->children()
                ->node('child', 'variable')->info('child info')->defaultValue('default')->example('example')
            ->end()
        ->end();

        $tree = $builder->buildTree();
        $children = $tree->getChildren();

        $this->assertTrue(is_array($tree->getExample()));
        $this->assertEquals('example', $children['child']->getExample());
    }
}
