<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Constraints_CountryValidatorTest extends Symfony_Component_Validator_Tests_Constraints_LocalizedTestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        parent::setUp();

        $this->context = $this->getMock('Symfony_Component_Validator_ExecutionContext', array(), array(), '', false);
        $this->validator = new Symfony_Component_Validator_Constraints_CountryValidator();
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

        $this->validator->validate(null, new Symfony_Component_Validator_Constraints_Country());
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('', new Symfony_Component_Validator_Constraints_Country());
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new stdClass(), new Symfony_Component_Validator_Constraints_Country());
    }

    /**
     * @dataProvider getValidCountries
     */
    public function testValidCountries($country)
    {
        if (!class_exists('Symfony_Component_Locale_Locale')) {
            $this->markTestSkipped('The "Locale" component is not available');
        }

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($country, new Symfony_Component_Validator_Constraints_Country());
    }

    public function getValidCountries()
    {
        return array(
            array('GB'),
            array('AT'),
            array('MY'),
        );
    }

    /**
     * @dataProvider getInvalidCountries
     */
    public function testInvalidCountries($country)
    {
        if (!class_exists('Symfony_Component_Locale_Locale')) {
            $this->markTestSkipped('The "Locale" component is not available');
        }

        $constraint = new Symfony_Component_Validator_Constraints_Country(array(
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ value }}' => $country,
            ));

        $this->validator->validate($country, $constraint);
    }

    public function getInvalidCountries()
    {
        return array(
            array('foobar'),
            array('EN'),
        );
    }
}
