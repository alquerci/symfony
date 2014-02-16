<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Creates and configures new validator objects
 *
 * Usually you will use the static method buildDefault() to initialize a
 * factory with default configuration. To this method you can pass various
 * parameters that configure where the validator mapping is found. If you
 * don't pass a parameter, the mapping will be read from annotations.
 *
 * <code>
 * // read from annotations only
 * $factory = Symfony_Component_Validator_ValidatorFactory::buildDefault();
 *
 * // read from XML and YAML, suppress annotations
 * $factory = Symfony_Component_Validator_ValidatorFactory::buildDefault(array(
 *   '/path/to/mapping.xml',
 *   '/path/to/other/mapping.yml',
 * ), false);
 * </code>
 *
 * You then have to call getValidator() to create new validators.
 *
 * <code>
 * $validator = $factory->getValidator();
 * </code>
 *
 * When manually constructing a factory, the default configuration of the
 * validators can be passed to the constructor as a ValidatorContextInterface
 * object.
 *
 * <code>
 * $defaultContext = new ValidatorContext();
 * $defaultContext->setClassMetadataFactory($metadataFactory);
 * $defaultContext->setConstraintValidatorFactory($validatorFactory);
 * $factory = new Symfony_Component_Validator_ValidatorFactory($defaultContext);
 *
 * $form = $factory->getValidator();
 * </code>
 *
 * You can also override the default configuration by calling any of the
 * methods in this class. These methods return a ValidatorContextInterface object
 * on which you can override further settings or call getValidator() to create
 * a form.
 *
 * <code>
 * $form = $factory
 *     ->setClassMetadataFactory($customFactory);
 *     ->getValidator();
 * </code>
 *
 * Symfony_Component_Validator_ValidatorFactory instances should be cached and reused in your application.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @deprecated Deprecated since version 2.1, to be removed in 2.3. Use
 *             {@link Validation::createValidatorBuilder()} instead.
 */
class Symfony_Component_Validator_ValidatorFactory implements Symfony_Component_Validator_ValidatorContextInterface
{
    /**
     * Holds the context with the default configuration
     * @var Symfony_Component_Validator_ValidatorContextInterface
     */
    protected $defaultContext;

    /**
     * Builds a validator factory with the default mapping loaders
     *
     * @param array $mappingFiles A list of XML or YAML file names
     *                                      where mapping information can be
     *                                      found. Can be empty.
     * @param Boolean $annotations Whether to use annotations for
     *                                      retrieving mapping information
     * @param string $staticMethod The name of the static method to
     *                                      use, if static method loading should
     *                                      be enabled
     *
     * @return Symfony_Component_Validator_ValidatorFactory             The validator factory.
     *
     * @throws Symfony_Component_Validator_Exception_MappingException             If any of the files in $mappingFiles
     *                                      has neither the extension ".xml" nor
     *                                      ".yml" nor ".yaml"
     * @throws RuntimeException            If annotations are not supported.
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3. Use
     *             {@link Validation::createValidatorBuilder()} instead.
     */
    public static function buildDefault(array $mappingFiles = array(), $annotations = false, $staticMethod = null)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('buildDefault() is deprecated since version 2.1 and will be removed in 2.3. Use Validation::createValidatorBuilder() instead.', E_USER_DEPRECATED);

        $xmlMappingFiles = array();
        $yamlMappingFiles = array();
        $loaders = array();
        $context = new Symfony_Component_Validator_ValidatorContext();

        foreach ($mappingFiles as $file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            if ($extension === 'xml') {
                $xmlMappingFiles[] = $file;
            } elseif ($extension === 'yaml' || $extension === 'yml') {
                $yamlMappingFiles[] = $file;
            } else {
                throw new Symfony_Component_Validator_Exception_MappingException('The only supported mapping file formats are XML and YAML');
            }
        }

        if (count($xmlMappingFiles) > 0) {
            $loaders[] = new Symfony_Component_Validator_Mapping_Loader_XmlFilesLoader($xmlMappingFiles);
        }

        if (count($yamlMappingFiles) > 0) {
            $loaders[] = new Symfony_Component_Validator_Mapping_Loader_YamlFilesLoader($yamlMappingFiles);
        }

        if ($annotations) {
            if (!class_exists('Doctrine_Common_Annotations_AnnotationReader')) {
                throw new RuntimeException('Requested a ValidatorFactory with an AnnotationLoader, but the AnnotationReader was not found. You should add Doctrine Common to your project.');
            }

            $loaders[] = new Symfony_Component_Validator_Mapping_Loader_AnnotationLoader(new Doctrine_Common_Annotations_AnnotationReader());
        }

        if ($staticMethod) {
            $loaders[] = new Symfony_Component_Validator_Mapping_Loader_StaticMethodLoader($staticMethod);
        }

        if (count($loaders) > 1) {
            $loader = new Symfony_Component_Validator_Mapping_Loader_LoaderChain($loaders);
        } elseif (count($loaders) === 1) {
            $loader = $loaders[0];
        } else {
            throw new Symfony_Component_Validator_Exception_MappingException('No mapping loader was found for the given parameters');
        }

        $context->setClassMetadataFactory(new Symfony_Component_Validator_Mapping_ClassMetadataFactory($loader));
        $context->setConstraintValidatorFactory(new Symfony_Component_Validator_ConstraintValidatorFactory());

        return new self($context);
    }

    /**
     * Sets the given context as default context
     *
     * @param Symfony_Component_Validator_ValidatorContextInterface $defaultContext A preconfigured context
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3. Use
     *             {@link Symfony_Component_Validator_Validation::createValidatorBuilder()} instead.
     */
    public function __construct(Symfony_Component_Validator_ValidatorContextInterface $defaultContext = null)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('ValidatorFactory is deprecated since version 2.1 and will be removed in 2.3. Use Validation::createValidatorBuilder() instead.', E_USER_DEPRECATED);

        $this->defaultContext = null === $defaultContext ? new Symfony_Component_Validator_ValidatorContext() : $defaultContext;
    }

    /**
     * Overrides the class metadata factory of the default context and returns
     * the new context
     *
     * @param Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface $metadataFactory The new factory instance
     *
     * @return Symfony_Component_Validator_ValidatorContextInterface                       The preconfigured form context
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3. Use
     *             {@link Symfony_Component_Validator_Validation::createValidatorBuilder()} instead.
     */
    public function setClassMetadataFactory(Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface $metadataFactory)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('setClassMetadataFactory() is deprecated since version 2.1 and will be removed in 2.3. Use Validation::createValidatorBuilder() instead.', E_USER_DEPRECATED);

        $context = clone $this->defaultContext;

        return $context->setClassMetadataFactory($metadataFactory);
    }

    /**
     * Overrides the constraint validator factory of the default context and
     * returns the new context
     *
     * @param Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface $validatorFactory The new factory instance
     *
     * @return Symfony_Component_Validator_ValidatorContextInterface                        The preconfigured form context
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3. Use
     *             {@link Symfony_Component_Validator_Validation::createValidatorBuilder()} instead.
     */
    public function setConstraintValidatorFactory(Symfony_Component_Validator_ConstraintValidatorFactoryInterface $validatorFactory)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('setConstraintValidatorFactory() is deprecated since version 2.1 and will be removed in 2.3. Use Validation::createValidatorBuilder() instead.', E_USER_DEPRECATED);

        $context = clone $this->defaultContext;

        return $context->setConstraintValidatorFactory($validatorFactory);
    }

    /**
     * Creates a new validator with the settings stored in the default context
     *
     * @return Symfony_Component_Validator_ValidatorInterface  The new validator
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3. Use
     *             {@link Symfony_Component_Validator_Validation::createValidator()} instead.
     */
    public function getValidator()
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('getValidator() is deprecated since version 2.1 and will be removed in 2.3. Use Validation::createValidator() instead.', E_USER_DEPRECATED);

        return $this->defaultContext->getValidator();
    }
}
