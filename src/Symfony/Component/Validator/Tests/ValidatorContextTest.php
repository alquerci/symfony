<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_ValidatorContextTest extends PHPUnit_Framework_TestCase
{
    protected $context;

    protected function setUp()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $this->context = new Symfony_Component_Validator_ValidatorContext();
    }

    protected function tearDown()
    {
        restore_error_handler();

        $this->context = null;
    }

    public function deprecationErrorHandler($errorNumber, $message, $file, $line, $context)
    {
        if ($errorNumber & E_USER_DEPRECATED) {
            return true;
        }

        return PHPUnit_Util_ErrorHandler::handleError($errorNumber, $message, $file, $line);
    }

    public function testSetClassMetadataFactory()
    {
        $factory = $this->getMock('Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface');
        $result = $this->context->setClassMetadataFactory($factory);

        $this->assertSame($this->context, $result);
        $this->assertSame($factory, $this->context->getClassMetadataFactory());
    }

    public function testSetConstraintValidatorFactory()
    {
        $factory = $this->getMock('Symfony_Component_Validator_ConstraintValidatorFactoryInterface');
        $result = $this->context->setConstraintValidatorFactory($factory);

        $this->assertSame($this->context, $result);
        $this->assertSame($factory, $this->context->getConstraintValidatorFactory());
    }

    public function testGetValidator()
    {
        $metadataFactory = $this->getMock('Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface');
        $validatorFactory = $this->getMock('Symfony_Component_Validator_ConstraintValidatorFactoryInterface');

        $validator = $this->context
            ->setClassMetadataFactory($metadataFactory)
            ->setConstraintValidatorFactory($validatorFactory)
            ->getValidator();

        $this->assertEquals(new Symfony_Component_Validator_Validator(new Symfony_Component_Validator_Mapping_ClassMetadataFactoryAdapter($metadataFactory), $validatorFactory, new Symfony_Component_Validator_DefaultTranslator()), $validator);
    }
}
