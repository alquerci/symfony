<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Config_Tests_Definition_ArrayNodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Symfony_Component_Config_Definition_Exception_InvalidTypeException
     */
    public function testNormalizeThrowsExceptionWhenFalseIsNotAllowed()
    {
        $node = new Symfony_Component_Config_Definition_ArrayNode('root');
        $node->normalize(false);
    }

    /**
     * @expectedException Symfony_Component_Config_Definition_Exception_InvalidConfigurationException
     * @expectedExceptionMessage Unrecognized options "foo" under "root"
     */
    public function testExceptionThrownOnUnrecognizedChild()
    {
        $node = new Symfony_Component_Config_Definition_ArrayNode('root');
        $node->normalize(array('foo' => 'bar'));
    }

    /**
     * Tests that no exception is thrown for an unrecognized child if the
     * ignoreExtraKeys option is set to true.
     *
     * Related to testExceptionThrownOnUnrecognizedChild
     */
    public function testIgnoreExtraKeysNoException()
    {
        $node = new Symfony_Component_Config_Definition_ArrayNode('roo');
        $node->setIgnoreExtraKeys(true);

        $node->normalize(array('foo' => 'bar'));
        $this->assertTrue(true, 'No exception was thrown when setIgnoreExtraKeys is true');
    }

    /**
     * @dataProvider getPreNormalizationTests
     */
    public function testPreNormalize($denormalized, $normalized)
    {
        $node = new Symfony_Component_Config_Tests_Definition_ArrayNode('foo');

        $this->assertSame($normalized, $node->preNormalize($denormalized));
    }

    public function getPreNormalizationTests()
    {
        return array(
            array(
                array('foo-bar' => 'foo'),
                array('foo_bar' => 'foo'),
            ),
            array(
                array('foo-bar_moo' => 'foo'),
                array('foo-bar_moo' => 'foo'),
            ),
            array(
                array('foo-bar' => null, 'foo_bar' => 'foo'),
                array('foo-bar' => null, 'foo_bar' => 'foo'),
            )
        );
    }
}

class Symfony_Component_Config_Tests_Definition_ArrayNode extends Symfony_Component_Config_Definition_ArrayNode
{
    public function preNormalize($value)
    {
        return parent::preNormalize($value);
    }
}
