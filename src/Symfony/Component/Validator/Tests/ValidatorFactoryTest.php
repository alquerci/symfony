<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_ValidatorFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $defaultContext;
    protected $factory;

    protected function setUp()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $this->defaultContext = new Symfony_Component_Validator_ValidatorContext();
        $this->factory = new Symfony_Component_Validator_ValidatorFactory($this->defaultContext);
    }

    protected function tearDown()
    {
        restore_error_handler();

        $this->defaultContext = null;
        $this->factory = null;
    }

    public function deprecationErrorHandler($errorNumber, $message, $file, $line, $context)
    {
        if ($errorNumber & E_USER_DEPRECATED) {
            return true;
        }

        return PHPUnit_Util_ErrorHandler::handleError($errorNumber, $message, $file, $line);
    }

    public function testOverrideClassMetadataFactory()
    {
        $factory1 = $this->getMock('Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface');
        $factory2 = $this->getMock('Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface');

        $this->defaultContext->setClassMetadataFactory($factory1);

        $result = $this->factory->setClassMetadataFactory($factory2);

        $this->assertSame($factory1, $this->defaultContext->getClassMetadataFactory());
        $this->assertSame($factory2, $result->getClassMetadataFactory());
    }

    public function testOverrideConstraintValidatorFactory()
    {
        $factory1 = $this->getMock('Symfony_Component_Validator_ConstraintValidatorFactoryInterface');
        $factory2 = $this->getMock('Symfony_Component_Validator_ConstraintValidatorFactoryInterface');

        $this->defaultContext->setConstraintValidatorFactory($factory1);

        $result = $this->factory->setConstraintValidatorFactory($factory2);

        $this->assertSame($factory1, $this->defaultContext->getConstraintValidatorFactory());
        $this->assertSame($factory2, $result->getConstraintValidatorFactory());
    }

    public function testGetValidator()
    {
        $metadataFactory = $this->getMock('Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface');
        $validatorFactory = $this->getMock('Symfony_Component_Validator_ConstraintValidatorFactoryInterface');

        $this->defaultContext
            ->setClassMetadataFactory($metadataFactory)
            ->setConstraintValidatorFactory($validatorFactory);

        $validator = $this->factory->getValidator();

        $this->assertEquals(new Symfony_Component_Validator_Validator(new Symfony_Component_Validator_Mapping_ClassMetadataFactoryAdapter($metadataFactory), $validatorFactory, new Symfony_Component_Validator_DefaultTranslator()), $validator);
    }

    public function testBuildDefaultFromAnnotationsWithCustomNamespaces()
    {
        if (!class_exists('Doctrine_Common_Annotations_AnnotationReader')) {
            $this->markTestSkipped('Annotations is required for this test');
        }
        $factory = Symfony_Component_Validator_ValidatorFactory::buildDefault(array(), true);

        $context = new Symfony_Component_Validator_ValidatorContext();
        $context
            ->setClassMetadataFactory(new Symfony_Component_Validator_Mapping_ClassMetadataFactory(new Symfony_Component_Validator_Mapping_Loader_AnnotationLoader(new Doctrine_Common_Annotations_AnnotationReader())))
            ->setConstraintValidatorFactory(new Symfony_Component_Validator_ConstraintValidatorFactory());

        $this->assertEquals(new Symfony_Component_Validator_ValidatorFactory($context), $factory);
    }

    public function testBuildDefaultFromXml()
    {
        $path = dirname(__FILE__).'/Mapping/Loader/constraint-mapping.xml';
        $factory = Symfony_Component_Validator_ValidatorFactory::buildDefault(array($path), false);

        $context = new Symfony_Component_Validator_ValidatorContext();
        $context
            ->setClassMetadataFactory(new Symfony_Component_Validator_Mapping_ClassMetadataFactory(new Symfony_Component_Validator_Mapping_Loader_XmlFilesLoader(array($path))))
            ->setConstraintValidatorFactory(new Symfony_Component_Validator_ConstraintValidatorFactory());

        $this->assertEquals(new Symfony_Component_Validator_ValidatorFactory($context), $factory);
    }

    public function testBuildDefaultFromYaml()
    {
        $path = dirname(__FILE__).'/Mapping/Loader/constraint-mapping.yml';
        $factory = Symfony_Component_Validator_ValidatorFactory::buildDefault(array($path), false);

        $context = new Symfony_Component_Validator_ValidatorContext();
        $context
            ->setClassMetadataFactory(new Symfony_Component_Validator_Mapping_ClassMetadataFactory(new Symfony_Component_Validator_Mapping_Loader_YamlFilesLoader(array($path))))
            ->setConstraintValidatorFactory(new Symfony_Component_Validator_ConstraintValidatorFactory());

        $this->assertEquals(new Symfony_Component_Validator_ValidatorFactory($context), $factory);
    }

    public function testBuildDefaultFromStaticMethod()
    {
        $path = dirname(__FILE__).'/Mapping/Loader/constraint-mapping.yml';
        $factory = Symfony_Component_Validator_ValidatorFactory::buildDefault(array(), false, 'loadMetadata');

        $context = new Symfony_Component_Validator_ValidatorContext();
        $context
            ->setClassMetadataFactory(new Symfony_Component_Validator_Mapping_ClassMetadataFactory(new Symfony_Component_Validator_Mapping_Loader_StaticMethodLoader('loadMetadata')))
            ->setConstraintValidatorFactory(new Symfony_Component_Validator_ConstraintValidatorFactory());

        $this->assertEquals(new Symfony_Component_Validator_ValidatorFactory($context), $factory);
    }

    public function testBuildDefaultFromMultipleLoaders()
    {
        if (!class_exists('Doctrine_Common_Annotations_AnnotationReader')) {
            $this->markTestSkipped('Annotations is required for this test');
        }
        $xmlPath = dirname(__FILE__).'/Mapping/Loader/constraint-mapping.xml';
        $yamlPath = dirname(__FILE__).'/Mapping/Loader/constraint-mapping.yml';
        $factory = Symfony_Component_Validator_ValidatorFactory::buildDefault(array($xmlPath, $yamlPath), true, 'loadMetadata');

        $chain = new Symfony_Component_Validator_Mapping_Loader_LoaderChain(array(
            new Symfony_Component_Validator_Mapping_Loader_XmlFilesLoader(array($xmlPath)),
            new Symfony_Component_Validator_Mapping_Loader_YamlFilesLoader(array($yamlPath)),
            new Symfony_Component_Validator_Mapping_Loader_AnnotationLoader(new Doctrine_Common_Annotations_AnnotationReader()),
            new Symfony_Component_Validator_Mapping_Loader_StaticMethodLoader('loadMetadata'),
        ));

        $context = new Symfony_Component_Validator_ValidatorContext();
        $context
            ->setClassMetadataFactory(new Symfony_Component_Validator_Mapping_ClassMetadataFactory($chain))
            ->setConstraintValidatorFactory(new Symfony_Component_Validator_ConstraintValidatorFactory());

        $this->assertEquals(new Symfony_Component_Validator_ValidatorFactory($context), $factory);
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_MappingException
     */
    public function testBuildDefaultThrowsExceptionIfNoLoaderIsFound()
    {
        Symfony_Component_Validator_ValidatorFactory::buildDefault();
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_MappingException
     */
    public function testBuildDefaultThrowsExceptionIfUnknownExtension()
    {
        Symfony_Component_Validator_ValidatorFactory::buildDefault(array(
            dirname(__FILE__).'/Mapping/Loader/StaticMethodLoaderTest.php'
        ));
    }
}
