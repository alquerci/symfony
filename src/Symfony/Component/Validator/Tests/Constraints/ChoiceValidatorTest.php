<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function Symfony_Component_Validator_Tests_Constraints_choice_callback()
{
    return array('foo', 'bar');
}

class Symfony_Component_Validator_Tests_Constraints_ChoiceValidatorTest extends PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;

    public static function staticCallback()
    {
        return array('foo', 'bar');
    }

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony_Component_Validator_ExecutionContext', array(), array(), '', false);
        $this->validator = new Symfony_Component_Validator_Constraints_ChoiceValidator();
        $this->validator->initialize($this->context);

        $this->context->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue(__CLASS__));
    }

    protected function tearDown()
    {
        $this->context = null;
        $this->validator = null;
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_UnexpectedTypeException
     */
    public function testExpectArrayIfMultipleIsTrue()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array(
            'choices' => array('foo', 'bar'),
            'multiple' => true,
        ));

        $this->validator->validate('asdf', $constraint);
    }

    public function testNullIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(null, new Symfony_Component_Validator_Constraints_Choice(array('choices' => array('foo', 'bar'))));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_ConstraintDefinitionException
     */
    public function testChoicesOrCallbackExpected()
    {
        $this->validator->validate('foobar', new Symfony_Component_Validator_Constraints_Choice());
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_ConstraintDefinitionException
     */
    public function testValidCallbackExpected()
    {
        $this->validator->validate('foobar', new Symfony_Component_Validator_Constraints_Choice(array('callback' => 'abcd')));
    }

    public function testValidChoiceArray()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array('choices' => array('foo', 'bar')));

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('bar', $constraint);
    }

    public function testValidChoiceCallbackFunction()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array('callback' => 'Symfony_Component_Validator_Tests_Constraints_choice_callback'));

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('bar', $constraint);
    }

    public function testValidChoiceCallbackClosure()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array('callback' => create_function('', '
            return array("foo", "bar");
        ')));

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('bar', $constraint);
    }

    public function testValidChoiceCallbackStaticMethod()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array('callback' => array(__CLASS__, 'staticCallback')));

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('bar', $constraint);
    }

    public function testValidChoiceCallbackContextMethod()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array('callback' => 'staticCallback'));

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('bar', $constraint);
    }

    public function testMultipleChoices()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array(
            'choices' => array('foo', 'bar', 'baz'),
            'multiple' => true,
        ));

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(array('baz', 'bar'), $constraint);
    }

    public function testInvalidChoice()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array(
            'choices' => array('foo', 'bar'),
            'message' => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ value }}' => 'baz',
            ));

        $this->validator->validate('baz', $constraint);
    }

    public function testInvalidChoiceMultiple()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array(
            'choices' => array('foo', 'bar'),
            'multipleMessage' => 'myMessage',
            'multiple' => true,
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ value }}' => 'baz',
            ));

        $this->validator->validate(array('foo', 'baz'), $constraint);
    }

    public function testTooFewChoices()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array(
            'choices' => array('foo', 'bar', 'moo', 'maa'),
            'multiple' => true,
            'min' => 2,
            'minMessage' => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ limit }}' => 2,
            ), null, 2);

        $this->validator->validate(array('foo'), $constraint);
    }

    public function testTooManyChoices()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array(
            'choices' => array('foo', 'bar', 'moo', 'maa'),
            'multiple' => true,
            'max' => 2,
            'maxMessage' => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ limit }}' => 2,
            ), null, 2);

        $this->validator->validate(array('foo', 'bar', 'moo'), $constraint);
    }

    public function testNonStrict()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array(
            'choices' => array(1, 2),
            'strict' => false,
        ));

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('2', $constraint);
        $this->validator->validate(2, $constraint);
    }

    public function testStrictAllowsExactValue()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array(
            'choices' => array(1, 2),
            'strict' => true,
        ));

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(2, $constraint);
    }

    public function testStrictDisallowsDifferentType()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array(
            'choices' => array(1, 2),
            'strict' => true,
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ value }}' => '2',
            ));

        $this->validator->validate('2', $constraint);
    }

    public function testNonStrictWithMultipleChoices()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array(
            'choices' => array(1, 2, 3),
            'multiple' => true,
            'strict' => false
        ));

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(array('2', 3), $constraint);
    }

    public function testStrictWithMultipleChoices()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Choice(array(
            'choices' => array(1, 2, 3),
            'multiple' => true,
            'strict' => true,
            'multipleMessage' => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ value }}' => '3',
            ));

        $this->validator->validate(array(2, '3'), $constraint);
    }
}
