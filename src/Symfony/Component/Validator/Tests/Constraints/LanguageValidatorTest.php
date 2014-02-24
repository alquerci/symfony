<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Constraints_LanguageValidatorTest extends Symfony_Component_Validator_Tests_Constraints_LocalizedTestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        parent::setUp();

        $this->context = $this->getMock('Symfony_Component_Validator_ExecutionContext', array(), array(), '', false);
        $this->validator = new Symfony_Component_Validator_Constraints_LanguageValidator();
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

        $this->validator->validate(null, new Symfony_Component_Validator_Constraints_Language());
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('', new Symfony_Component_Validator_Constraints_Language());
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new stdClass(), new Symfony_Component_Validator_Constraints_Language());
    }

    /**
     * @dataProvider getValidLanguages
     */
    public function testValidLanguages($language)
    {
        if (!class_exists('Symfony_Component_Locale_Locale')) {
            $this->markTestSkipped('The "Locale" component is not available');
        }

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($language, new Symfony_Component_Validator_Constraints_Language());
    }

    public function getValidLanguages()
    {
        return array(
            array('en'),
            array('en_US'),
            array('my'),
        );
    }

    /**
     * @dataProvider getInvalidLanguages
     */
    public function testInvalidLanguages($language)
    {
        if (!class_exists('Symfony_Component_Locale_Locale')) {
            $this->markTestSkipped('The "Locale" component is not available');
        }

        $constraint = new Symfony_Component_Validator_Constraints_Language(array(
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ value }}' => $language,
            ));

        $this->validator->validate($language, $constraint);
    }

    public function getInvalidLanguages()
    {
        return array(
            array('EN'),
            array('foobar'),
        );
    }
}
