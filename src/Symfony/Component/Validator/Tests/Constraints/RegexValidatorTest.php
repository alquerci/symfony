<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Constraints_RegexValidatorTest extends PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony_Component_Validator_ExecutionContext', array(), array(), '', false);
        $this->validator = new Symfony_Component_Validator_Constraints_RegexValidator();
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

        $this->validator->validate(null, new Symfony_Component_Validator_Constraints_Regex(array('pattern' => '/^[0-9]+$/')));
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('', new Symfony_Component_Validator_Constraints_Regex(array('pattern' => '/^[0-9]+$/')));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new stdClass(), new Symfony_Component_Validator_Constraints_Regex(array('pattern' => '/^[0-9]+$/')));
    }

    /**
     * @dataProvider getValidValues
     */
    public function testValidValues($value)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $constraint = new Symfony_Component_Validator_Constraints_Regex(array('pattern' => '/^[0-9]+$/'));
        $this->validator->validate($value, $constraint);
    }

    public function getValidValues()
    {
        return array(
            array(0),
            array('0'),
            array('090909'),
            array(90909),
        );
    }

    /**
     * @dataProvider getInvalidValues
     */
    public function testInvalidValues($value)
    {
        $constraint = new Symfony_Component_Validator_Constraints_Regex(array(
            'pattern' => '/^[0-9]+$/',
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ value }}' => $value,
            ));

        $this->validator->validate($value, $constraint);
    }

    public function getInvalidValues()
    {
        return array(
            array('abcd'),
            array('090foo'),
        );
    }

    public function testConstraintGetDefaultOption()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Regex(array(
            'pattern' => '/^[0-9]+$/',
        ));

        $this->assertEquals('pattern', $constraint->getDefaultOption());
    }

    public function testHtmlPatternEscaping()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Regex(array(
            'pattern' => '/^[0-9]+\/$/',
        ));

        $this->assertEquals('[0-9]+/', $constraint->getHtmlPattern());

        $constraint = new Symfony_Component_Validator_Constraints_Regex(array(
            'pattern' => '#^[0-9]+\#$#',
        ));

        $this->assertEquals('[0-9]+#', $constraint->getHtmlPattern());
    }

    public function testHtmlPattern()
    {
        // Specified htmlPattern
        $constraint = new Symfony_Component_Validator_Constraints_Regex(array(
            'pattern' => '/^[a-z]+$/i',
            'htmlPattern' => '[a-zA-Z]+',
        ));
        $this->assertEquals('[a-zA-Z]+', $constraint->getHtmlPattern());

        // Disabled htmlPattern
        $constraint = new Symfony_Component_Validator_Constraints_Regex(array(
            'pattern' => '/^[a-z]+$/i',
            'htmlPattern' => false,
        ));
        $this->assertNull($constraint->getHtmlPattern());

        // Cannot be converted
        $constraint = new Symfony_Component_Validator_Constraints_Regex(array(
            'pattern' => '/^[a-z]+$/i',
        ));
        $this->assertNull($constraint->getHtmlPattern());

        // Automatically converted
        $constraint = new Symfony_Component_Validator_Constraints_Regex(array(
            'pattern' => '/^[a-z]+$/',
        ));
        $this->assertEquals('[a-z]+', $constraint->getHtmlPattern());

        // Automatically converted, adds .*
        $constraint = new Symfony_Component_Validator_Constraints_Regex(array(
            'pattern' => '/[a-z]+/',
        ));
        $this->assertEquals('.*[a-z]+.*', $constraint->getHtmlPattern());

        // Dropped because of match=false
        $constraint = new Symfony_Component_Validator_Constraints_Regex(array(
            'pattern' => '/[a-z]+/',
            'match' => false
        ));
        $this->assertNull($constraint->getHtmlPattern());
    }
}
