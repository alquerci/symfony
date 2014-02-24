<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Constraints_NotBlankValidatorTest extends PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony_Component_Validator_ExecutionContext', array(), array(), '', false);
        $this->validator = new Symfony_Component_Validator_Constraints_NotBlankValidator();
        $this->validator->initialize($this->context);
    }

    protected function tearDown()
    {
        $this->context = null;
        $this->validator = null;
    }

    /**
     * @dataProvider getValidValues
     */
    public function testValidValues($date)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($date, new Symfony_Component_Validator_Constraints_NotBlank());
    }

    public function getValidValues()
    {
        return array(
            array('foobar'),
            array(0),
            array(0.0),
            array('0'),
            array(1234),
        );
    }

    public function testNullIsInvalid()
    {
        $constraint = new Symfony_Component_Validator_Constraints_NotBlank(array(
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage');

        $this->validator->validate(null, $constraint);
    }

    public function testBlankIsInvalid()
    {
        $constraint = new Symfony_Component_Validator_Constraints_NotBlank(array(
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage');

        $this->validator->validate('', $constraint);
    }

    public function testFalseIsInvalid()
    {
        $constraint = new Symfony_Component_Validator_Constraints_NotBlank(array(
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage');

        $this->validator->validate(false, $constraint);
    }

    public function testEmptyArrayIsInvalid()
    {
        $constraint = new Symfony_Component_Validator_Constraints_NotBlank(array(
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage');

        $this->validator->validate(array(), $constraint);
    }
}
