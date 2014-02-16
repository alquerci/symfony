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

class Symfony_Component_Validator_Tests_ValidatorBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony_Component_Validator_ValidatorBuilderInterface
     */
    protected $builder;

    protected function setUp()
    {
        $this->builder = new Symfony_Component_Validator_ValidatorBuilder();
    }

    protected function tearDown()
    {
        $this->builder = null;
    }

    public function deprecationErrorHandler($errorNumber, $message, $file, $line, $context)
    {
        if ($errorNumber & E_USER_DEPRECATED) {
            return true;
        }

        return PHPUnit_Util_ErrorHandler::handleError($errorNumber, $message, $file, $line);
    }

    public function testAddObjectInitializer()
    {
        $this->assertSame($this->builder, $this->builder->addObjectInitializer(
            $this->getMock('Symfony_Component_Validator_ObjectInitializerInterface')
        ));
    }

    public function testAddObjectInitializers()
    {
        $this->assertSame($this->builder, $this->builder->addObjectInitializers(array()));
    }

    public function testAddXmlMapping()
    {
        $this->assertSame($this->builder, $this->builder->addXmlMapping('mapping'));
    }

    public function testAddXmlMappings()
    {
        $this->assertSame($this->builder, $this->builder->addXmlMappings(array()));
    }

    public function testAddYamlMapping()
    {
        $this->assertSame($this->builder, $this->builder->addYamlMapping('mapping'));
    }

    public function testAddYamlMappings()
    {
        $this->assertSame($this->builder, $this->builder->addYamlMappings(array()));
    }

    public function testAddMethodMapping()
    {
        $this->assertSame($this->builder, $this->builder->addMethodMapping('mapping'));
    }

    public function testAddMethodMappings()
    {
        $this->assertSame($this->builder, $this->builder->addMethodMappings(array()));
    }

    public function testEnableAnnotationMapping()
    {
        if (!class_exists('Doctrine_Common_Annotations_AnnotationReader')) {
            $this->markTestSkipped('Annotations is required for this test');
        }

        $this->assertSame($this->builder, $this->builder->enableAnnotationMapping());
    }

    public function testDisableAnnotationMapping()
    {
        $this->assertSame($this->builder, $this->builder->disableAnnotationMapping());
    }

    public function testSetMetadataFactory()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));
        $this->assertSame($this->builder, $this->builder->setMetadataFactory(
            $this->getMock('Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface'))
        );
        restore_error_handler();
    }

    public function testSetMetadataCache()
    {
        $this->assertSame($this->builder, $this->builder->setMetadataCache(
            $this->getMock('Symfony_Component_Validator_Mapping_Cache_CacheInterface'))
        );
    }

    public function testSetConstraintValidatorFactory()
    {
        $this->assertSame($this->builder, $this->builder->setConstraintValidatorFactory(
            $this->getMock('Symfony_Component_Validator_ConstraintValidatorFactoryInterface'))
        );
    }

    public function testSetTranslator()
    {
        $this->assertSame($this->builder, $this->builder->setTranslator(
            $this->getMock('Symfony_Component_Translation_TranslatorInterface'))
        );
    }

    public function testSetTranslationDomain()
    {
        $this->assertSame($this->builder, $this->builder->setTranslationDomain('TRANS_DOMAIN'));
    }
}
