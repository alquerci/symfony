<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Config_Tests_Definition_Builder_NodeBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testThrowsAnExceptionWhenTryingToCreateANonRegisteredNodeType()
    {
        $builder = new Symfony_Component_Config_Definition_Builder_NodeBuilder();
        $builder->node('', 'foobar');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testThrowsAnExceptionWhenTheNodeClassIsNotFound()
    {
        $builder = new Symfony_Component_Config_Definition_Builder_NodeBuilder();
        $builder
            ->setNodeClass('noclasstype', '\\foo\\bar\\noclass')
            ->node('', 'noclasstype');
    }

    public function testAddingANewNodeType()
    {
        $class = 'Symfony_Component_Config_Tests_Definition_Builder_SomeNodeDefinition';

        $builder = new Symfony_Component_Config_Definition_Builder_NodeBuilder();
        $node = $builder
            ->setNodeClass('newtype', $class)
            ->node('', 'newtype');

        $this->assertEquals(get_class($node), $class);
    }

    public function testOverridingAnExistingNodeType()
    {
        $class = 'Symfony_Component_Config_Tests_Definition_Builder_SomeNodeDefinition';

        $builder = new Symfony_Component_Config_Definition_Builder_NodeBuilder();
        $node = $builder
            ->setNodeClass('variable', $class)
            ->node('', 'variable');

        $this->assertEquals(get_class($node), $class);
    }

    public function testNodeTypesAreNotCaseSensitive()
    {
        $builder = new Symfony_Component_Config_Definition_Builder_NodeBuilder();

        $node1 = $builder->node('', 'VaRiAbLe');
        $node2 = $builder->node('', 'variable');

        $this->assertEquals(get_class($node1), get_class($node2));

        $builder->setNodeClass('CuStOm', 'Symfony_Component_Config_Tests_Definition_Builder_SomeNodeDefinition');

        $node1 = $builder->node('', 'CUSTOM');
        $node2 = $builder->node('', 'custom');

        $this->assertEquals(get_class($node1), get_class($node2));
    }

    public function testNumericNodeCreation()
    {
        $builder = new Symfony_Component_Config_Tests_Fixtures_Builder_NodeBuilder();

        $node = $builder->integerNode('foo')->min(3)->max(5);
        $this->assertEquals('Symfony_Component_Config_Definition_Builder_IntegerNodeDefinition', get_class($node));

        $node = $builder->floatNode('bar')->min(3.0)->max(5.0);
        $this->assertEquals('Symfony_Component_Config_Definition_Builder_FloatNodeDefinition', get_class($node));
    }
}

class Symfony_Component_Config_Tests_Definition_Builder_SomeNodeDefinition extends Symfony_Component_Config_Definition_Builder_VariableNodeDefinition
{
}
