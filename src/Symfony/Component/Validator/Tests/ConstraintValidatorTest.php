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

class Symfony_Component_Validator_Tests_ConstraintValidatorTest_Validator extends Symfony_Component_Validator_ConstraintValidator
{
    private $message;
    private $params;

    public function __construct($message, array $params = array())
    {
        $this->message = $message;
        $this->params = $params;
    }

    public function deprecationErrorHandler($errorNumber, $message, $file, $line, $context)
    {
        if ($errorNumber & E_USER_DEPRECATED) {
            return true;
        }

        return PHPUnit_Util_ErrorHandler::handleError($errorNumber, $message, $file, $line);
    }

    public function validate($value, Symfony_Component_Validator_Constraint $constraint)
    {
        set_error_handler(array($this, "deprecationErrorHandler"));
        $this->setMessage($this->message, $this->params);
        restore_error_handler();
    }
}

class Symfony_Component_Validator_Tests_ConstraintValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testSetMessage()
    {
        $context = $this->getMock('Symfony_Component_Validator_ExecutionContext', array(), array(), '', false);
        $constraint = $this->getMock('Symfony_Component_Validator_Constraint', array(), array(), '', false);
        $validator = new Symfony_Component_Validator_Tests_ConstraintValidatorTest_Validator('error message', array('foo' => 'bar'));
        $validator->initialize($context);

        $context->expects($this->once())
            ->method('addViolation')
            ->with('error message', array('foo' => 'bar'));

        $validator->validate('bam', $constraint);
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_ValidatorException
     */
    public function testSetMessageFailsIfNoContextSet()
    {
        $constraint = $this->getMock('Symfony_Component_Validator_Constraint', array(), array(), '', false);
        $validator = new Symfony_Component_Validator_Tests_ConstraintValidatorTest_Validator('error message', array('foo' => 'bar'));

        $validator->validate('bam', $constraint);
    }
}
