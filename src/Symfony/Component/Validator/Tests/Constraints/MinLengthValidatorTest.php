<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('E_USER_DEPRECATED')) {
    define('E_USER_DEPRECATED', 16384);
}

class Symfony_Component_Validator_Tests_Constraints_MinLengthValidatorTest extends PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $this->context = $this->getMock('Symfony_Component_Validator_ExecutionContext', array(), array(), '', false);
        $this->validator = new Symfony_Component_Validator_Constraints_MinLengthValidator();
        $this->validator->initialize($this->context);
    }

    protected function tearDown()
    {
        restore_error_handler();

        $this->context = null;
        $this->validator = null;
    }

    public function deprecationErrorHandler($errorNumber, $message, $file, $line, $context)
    {
        if ($errorNumber & E_USER_DEPRECATED) {
            return true;
        }

        return PHPUnit_Util_ErrorHandler::handleError($errorNumber, $message, $file, $line);
    }

    public function testNullIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(null, new Symfony_Component_Validator_Constraints_MinLength(array('limit' => 6)));
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('', new Symfony_Component_Validator_Constraints_MinLength(array('limit' => 6)));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new stdClass(), new Symfony_Component_Validator_Constraints_MinLength(array('limit' => 5)));
    }

    /**
     * @dataProvider getValidValues
     */
    public function testValidValues($value, $mbOnly = false)
    {
        if ($mbOnly && !function_exists('mb_strlen')) {
            $this->markTestSkipped('mb_strlen does not exist');
        }

        $this->context->expects($this->never())
            ->method('addViolation');

        $constraint = new Symfony_Component_Validator_Constraints_MinLength(array('limit' => 6));
        $this->validator->validate($value, $constraint);
    }

    public function getValidValues()
    {
        return array(
            array(123456),
            array('123456'),
            array('üüüüüü', true),
            array('éééééé', true),
        );
    }

    /**
     * @dataProvider getInvalidValues
     */
    public function testInvalidValues($value, $mbOnly = false)
    {
        if ($mbOnly && !function_exists('mb_strlen')) {
            $this->markTestSkipped('mb_strlen does not exist');
        }

        $constraint = new Symfony_Component_Validator_Constraints_MinLength(array(
            'limit' => 5,
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', $this->identicalTo(array(
                '{{ value }}' => (string) $value,
                '{{ limit }}' => 5,
            )), $this->identicalTo($value), 5);

        $this->validator->validate($value, $constraint);
    }

    public function getInvalidValues()
    {
        return array(
            array(1234),
            array('1234'),
            array('üüüü', true),
            array('éééé', true),
        );
    }

    public function testConstraintGetDefaultOption()
    {
        $constraint = new Symfony_Component_Validator_Constraints_MinLength(array(
            'limit' => 5,
        ));

        $this->assertEquals('limit', $constraint->getDefaultOption());
    }
}
