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

class Symfony_Component_Validator_Tests_Constraints_MaxValidatorTest extends PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $this->context = $this->getMock('Symfony_Component_Validator_ExecutionContext', array(), array(), '', false);
        $this->validator = new Symfony_Component_Validator_Constraints_MaxValidator();
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

        $this->validator->validate(null, new Symfony_Component_Validator_Constraints_Max(array('limit' => 10)));
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('', new Symfony_Component_Validator_Constraints_Max(array('limit' => 10)));
    }

    /**
     * @dataProvider getValidValues
     */
    public function testValidValues($value)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $constraint = new Symfony_Component_Validator_Constraints_Max(array('limit' => 10));
        $this->validator->validate($value, $constraint);
    }

    public function getValidValues()
    {
        return array(
            array(9.999999),
            array(10),
            array(10.0),
            array('10'),
        );
    }

    /**
     * @dataProvider getInvalidValues
     */
    public function testInvalidValues($value)
    {
        $constraint = new Symfony_Component_Validator_Constraints_Max(array(
            'limit' => 10,
            'message' => 'myMessage',
            'invalidMessage' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ value }}' => $value,
                '{{ limit }}' => 10,
            ));

        $this->validator->validate($value, $constraint);
    }

    public function getInvalidValues()
    {
        return array(
            array(10.00001),
            array('10.00001'),
            array(new stdClass()),
        );
    }

    public function testConstraintGetDefaultOption()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Max(array(
            'limit' => 10,
        ));

        $this->assertEquals('limit', $constraint->getDefaultOption());
    }
}
