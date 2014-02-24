<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_DataTransformer_ValueToDuplicatesTransformerTest extends PHPUnit_Framework_TestCase
{
    private $transformer;

    protected function setUp()
    {
        $this->transformer = new Symfony_Component_Form_Extension_Core_DataTransformer_ValueToDuplicatesTransformer(array('a', 'b', 'c'));
    }

    protected function tearDown()
    {
        $this->transformer = null;
    }

    public function testTransform()
    {
        $output = array(
            'a' => 'Foo',
            'b' => 'Foo',
            'c' => 'Foo',
        );

        $this->assertSame($output, $this->transformer->transform('Foo'));
    }

    public function testTransformEmpty()
    {
        $output = array(
            'a' => null,
            'b' => null,
            'c' => null,
        );

        $this->assertSame($output, $this->transformer->transform(null));
    }

    public function testReverseTransform()
    {
        $input = array(
            'a' => 'Foo',
            'b' => 'Foo',
            'c' => 'Foo',
        );

        $this->assertSame('Foo', $this->transformer->reverseTransform($input));
    }

    public function testReverseTransformCompletelyEmpty()
    {
        $input = array(
            'a' => '',
            'b' => '',
            'c' => '',
        );

        $this->assertNull($this->transformer->reverseTransform($input));
    }

    public function testReverseTransformCompletelyNull()
    {
        $input = array(
            'a' => null,
            'b' => null,
            'c' => null,
        );

        $this->assertNull($this->transformer->reverseTransform($input));
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformPartiallyNull()
    {
        $input = array(
            'a' => 'Foo',
            'b' => 'Foo',
            'c' => null,
        );

        $this->transformer->reverseTransform($input);
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_TransformationFailedException
     */
    public function testReverseTransformDifferences()
    {
        $input = array(
            'a' => 'Foo',
            'b' => 'Bar',
            'c' => 'Foo',
        );

        $this->transformer->reverseTransform($input);
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_UnexpectedTypeException
     */
    public function testReverseTransformRequiresArray()
    {
        $this->transformer->reverseTransform('12345');
    }
}
