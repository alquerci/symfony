<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_DataTransformer_ChoiceToValueTransformerTest extends PHPUnit_Framework_TestCase
{
    protected $transformer;

    protected function setUp()
    {
        $list = new Symfony_Component_Form_Extension_Core_ChoiceList_SimpleChoiceList(array('' => 'A', 0 => 'B', 1 => 'C'));
        $this->transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_ChoiceToValueTransformer($list);
    }

    protected function tearDown()
    {
        $this->transformer = null;
    }

    public function transformProvider()
    {
        return array(
            // more extensive test set can be found in FormUtilTest
            array(0, '0'),
            array(false, '0'),
            array('', ''),
        );
    }

    /**
     * @dataProvider transformProvider
     */
    public function testTransform($in, $out)
    {
        $this->assertSame($out, $this->transformer->transform($in));
    }

    public function reverseTransformProvider()
    {
        return array(
            // values are expected to be valid choice keys already and stay
            // the same
            array('0', 0),
            array('', null),
            array(null, null),
        );
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($in, $out)
    {
        $this->assertSame($out, $this->transformer->reverseTransform($in));
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testReverseTransformExpectsScalar()
    {
        $this->transformer->reverseTransform(array());
    }
}
