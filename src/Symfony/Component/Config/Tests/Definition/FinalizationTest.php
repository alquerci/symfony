<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Config_Tests_Definition_FinalizationTest extends PHPUnit_Framework_TestCase
{
    public function testUnsetKeyWithDeepHierarchy()
    {
        $tb = new Symfony_Component_Config_Definition_Builder_TreeBuilder();
        $tree = $tb
            ->root('config', 'array')
                ->children()
                    ->node('level1', 'array')
                        ->canBeUnset()
                        ->children()
                            ->node('level2', 'array')
                                ->canBeUnset()
                                ->children()
                                    ->node('somevalue', 'scalar')->end()
                                    ->node('anothervalue', 'scalar')->end()
                                ->end()
                            ->end()
                            ->node('level1_scalar', 'scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->buildTree()
        ;

        $a = array(
            'level1' => array(
                'level2' => array(
                    'somevalue' => 'foo',
                    'anothervalue' => 'bar',
                ),
                'level1_scalar' => 'foo',
            ),
        );

        $b = array(
            'level1' => array(
                'level2' => false,
            ),
        );

        $this->assertEquals(array(
            'level1' => array(
                'level1_scalar' => 'foo',
            ),
        ), $this->process($tree, array($a, $b)));
    }

    protected function process(Symfony_Component_Config_Definition_NodeInterface $tree, array $configs)
    {
        $processor = new Symfony_Component_Config_Definition_Processor();

        return $processor->process($tree, $configs);
    }
}
