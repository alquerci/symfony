<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Constraints_TimeValidatorTest extends PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony_Component_Validator_ExecutionContext', array(), array(), '', false);
        $this->validator = new Symfony_Component_Validator_Constraints_TimeValidator();
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

        $this->validator->validate(null, new Symfony_Component_Validator_Constraints_Time());
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('', new Symfony_Component_Validator_Constraints_Time());
    }

    public function testDateTimeClassIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(new DateTime(), new Symfony_Component_Validator_Constraints_Time());
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new stdClass(), new Symfony_Component_Validator_Constraints_Time());
    }

    /**
     * @dataProvider getValidTimes
     */
    public function testValidTimes($time)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($time, new Symfony_Component_Validator_Constraints_Time());
    }

    public function getValidTimes()
    {
        return array(
            array('01:02:03'),
            array('00:00:00'),
            array('23:59:59'),
        );
    }

    /**
     * @dataProvider getInvalidTimes
     */
    public function testInvalidTimes($time)
    {
        $constraint = new Symfony_Component_Validator_Constraints_Time(array(
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ value }}' => $time,
            ));

        $this->validator->validate($time, $constraint);
    }

    public function getInvalidTimes()
    {
        return array(
            array('foobar'),
            array('foobar 12:34:56'),
            array('12:34:56 foobar'),
            array('00:00'),
            array('24:00:00'),
            array('00:60:00'),
            array('00:00:60'),
        );
    }
}
