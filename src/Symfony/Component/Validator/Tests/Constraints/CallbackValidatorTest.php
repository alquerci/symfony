<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Constraints_CallbackValidatorTest_Class
{
    public static function validateStatic($object, Symfony_Component_Validator_ExecutionContext $context)
    {
        $context->addViolation('Static message', array('{{ value }}' => 'foobar'), 'invalidValue');

        return false;
    }
}

class Symfony_Component_Validator_Tests_Constraints_CallbackValidatorTest_Object
{
    public function validateOne(Symfony_Component_Validator_ExecutionContext $context)
    {
        $context->addViolation('My message', array('{{ value }}' => 'foobar'), 'invalidValue');

        return false;
    }

    public function validateTwo(Symfony_Component_Validator_ExecutionContext $context)
    {
        $context->addViolation('Other message', array('{{ value }}' => 'baz'), 'otherInvalidValue');

        return false;
    }
}

class Symfony_Component_Validator_Tests_Constraints_CallbackValidatorTest extends PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony_Component_Validator_ExecutionContext', array(), array(), '', false);
        $this->validator = new Symfony_Component_Validator_Constraints_CallbackValidator();
        $this->validator->initialize($this->context);
    }

    protected function tearDown()
    {
        $this->context = null;
        $this->validator = null;
    }

    public function testNullIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(null, new Symfony_Component_Validator_Constraints_Callback(array('foo')));
    }

    public function testCallbackSingleMethod()
    {
        $object = new Symfony_Component_Validator_Tests_Constraints_CallbackValidatorTest_Object();
        $constraint = new Symfony_Component_Validator_Constraints_Callback(array('validateOne'));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('My message', array(
                '{{ value }}' => 'foobar',
            ));

        $this->validator->validate($object, $constraint);
    }

    public function testCallbackSingleStaticMethod()
    {
        $object = new Symfony_Component_Validator_Tests_Constraints_CallbackValidatorTest_Object();

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('Static message', array(
                '{{ value }}' => 'foobar',
            ));

        $this->validator->validate($object, new Symfony_Component_Validator_Constraints_Callback(array(
            array(__CLASS__.'_Class', 'validateStatic')
        )));
    }

    public function testCallbackMultipleMethods()
    {
        $object = new Symfony_Component_Validator_Tests_Constraints_CallbackValidatorTest_Object();

        $this->context->expects($this->at(0))
            ->method('addViolation')
            ->with('My message', array(
                '{{ value }}' => 'foobar',
            ));
        $this->context->expects($this->at(1))
            ->method('addViolation')
            ->with('Other message', array(
                '{{ value }}' => 'baz',
            ));

        $this->validator->validate($object, new Symfony_Component_Validator_Constraints_Callback(array(
            'validateOne', 'validateTwo'
        )));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_UnexpectedTypeException
     */
    public function testExpectCallbackArray()
    {
        $object = new Symfony_Component_Validator_Tests_Constraints_CallbackValidatorTest_Object();

        $this->validator->validate($object, new Symfony_Component_Validator_Constraints_Callback('foobar'));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_ConstraintDefinitionException
     */
    public function testExpectValidMethods()
    {
        $object = new Symfony_Component_Validator_Tests_Constraints_CallbackValidatorTest_Object();

        $this->validator->validate($object, new Symfony_Component_Validator_Constraints_Callback(array('foobar')));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_ConstraintDefinitionException
     */
    public function testExpectValidCallbacks()
    {
        $object = new Symfony_Component_Validator_Tests_Constraints_CallbackValidatorTest_Object();

        $this->validator->validate($object, new Symfony_Component_Validator_Constraints_Callback(array(array('foo', 'bar'))));
    }

    public function testConstraintGetTargets()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Callback(array('foo'));

        $this->assertEquals('class', $constraint->getTargets());
    }
}
