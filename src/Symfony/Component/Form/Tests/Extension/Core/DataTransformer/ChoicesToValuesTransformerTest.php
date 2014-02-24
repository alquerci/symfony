<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_DataTransformer_ChoicesToValuesTransformerTest extends PHPUnit_Framework_TestCase
{
    protected $transformer;

    protected function setUp()
    {
        $list = new Symfony_Component_Form_Extension_Core_ChoiceList_SimpleChoiceList(array(0 => 'A', 1 => 'B', 2 => 'C'));
        $this->transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_ChoicesToValuesTransformer($list);
    }

    protected function tearDown()
    {
        $this->transformer = null;
    }

    public function testTransform()
    {
        // Value strategy in SimpleChoiceList is to copy and convert to string
        $in = array(0, 1, 2);
        $out = array('0', '1', '2');

        $this->assertSame($out, $this->transformer->transform($in));
    }

    public function testTransformNull()
    {
        $this->assertSame(array(), $this->transformer->transform(null));
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testTransformExpectsArray()
    {
        $this->transformer->transform('foobar');
    }

    public function testReverseTransform()
    {
        // values are expected to be valid choices and stay the same
        $in = array('0', '1', '2');
        $out = array(0, 1, 2);

        $this->assertSame($out, $this->transformer->reverseTransform($in));
    }

    public function testReverseTransformNull()
    {
        $this->assertSame(array(), $this->transformer->reverseTransform(null));
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testReverseTransformExpectsArray()
    {
        $this->transformer->reverseTransform('foobar');
    }
}
