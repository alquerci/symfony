<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Constraints_EmailValidatorTest extends PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony_Component_Validator_ExecutionContext', array(), array(), '', false);
        $this->validator = new Symfony_Component_Validator_Constraints_EmailValidator();
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

        $this->validator->validate(null, new Symfony_Component_Validator_Constraints_Email());
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('', new Symfony_Component_Validator_Constraints_Email());
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new stdClass(), new Symfony_Component_Validator_Constraints_Email());
    }

    /**
     * @dataProvider getValidEmails
     */
    public function testValidEmails($email)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($email, new Symfony_Component_Validator_Constraints_Email());
    }

    public function getValidEmails()
    {
        return array(
            array('fabien@symfony.com'),
            array('example@example.co.uk'),
            array('fabien_potencier@example.fr'),
        );
    }

    /**
     * @dataProvider getInvalidEmails
     */
    public function testInvalidEmails($email)
    {
        $constraint = new Symfony_Component_Validator_Constraints_Email(array(
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ value }}' => $email,
            ));

        $this->validator->validate($email, $constraint);
    }

    public function getInvalidEmails()
    {
        return array(
            array('example'),
            array('example@'),
            array('example@localhost'),
            array('example@example.com@example.com'),
        );
    }
}
