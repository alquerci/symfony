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
 * A configurable builder for ValidatorInterface objects.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Symfony_Component_Validator_ValidatorBuilderInterface
{
    /**
     * Adds an object initializer to the validator.
     *
     * @param Symfony_Component_Validator_ObjectInitializerInterface $initializer The initializer.
     *
     * @return Symfony_Component_Validator_ValidatorBuilderInterface The builder object.
     */
    public function addObjectInitializer(Symfony_Component_Validator_ObjectInitializerInterface $initializer);

    /**
     * Adds a list of object initializers to the validator.
     *
     * @param array $initializers The initializer.
     *
     * @return Symfony_Component_Validator_ValidatorBuilderInterface The builder object.
     */
    public function addObjectInitializers(array $initializers);

    /**
     * Adds an XML constraint mapping file to the validator.
     *
     * @param string $path The path to the mapping file.
     *
     * @return Symfony_Component_Validator_ValidatorBuilderInterface The builder object.
     */
    public function addXmlMapping($path);

    /**
     * Adds a list of XML constraint mapping files to the validator.
     *
     * @param array $paths The paths to the mapping files.
     *
     * @return Symfony_Component_Validator_ValidatorBuilderInterface The builder object.
     */
    public function addXmlMappings(array $paths);

    /**
     * Adds a YAML constraint mapping file to the validator.
     *
     * @param string $path The path to the mapping file.
     *
     * @return Symfony_Component_Validator_ValidatorBuilderInterface The builder object.
     */
    public function addYamlMapping($path);

    /**
     * Adds a list of YAML constraint mappings file to the validator.
     *
     * @param array $paths The paths to the mapping files.
     *
     * @return Symfony_Component_Validator_ValidatorBuilderInterface The builder object.
     */
    public function addYamlMappings(array $paths);

    /**
     * Enables constraint mapping using the given static method.
     *
     * @param string $methodName The name of the method.
     *
     * @return Symfony_Component_Validator_ValidatorBuilderInterface The builder object.
     */
    public function addMethodMapping($methodName);

    /**
     * Enables constraint mapping using the given static methods.
     *
     * @param array $methodNames The names of the methods.
     *
     * @return Symfony_Component_Validator_ValidatorBuilderInterface The builder object.
     */
    public function addMethodMappings(array $methodNames);

    /**
     * Enables annotation based constraint mapping.
     *
     * @param Doctrine_Common_Annotations_Reader $annotationReader The annotation reader to be used.
     *
     * @return Symfony_Component_Validator_ValidatorBuilderInterface The builder object.
     */
    public function enableAnnotationMapping(Doctrine_Common_Annotations_Reader $annotationReader = null);

    /**
     * Disables annotation based constraint mapping.
     *
     * @return Symfony_Component_Validator_ValidatorBuilderInterface The builder object.
     */
    public function disableAnnotationMapping();

    /**
     * Sets the class metadata factory used by the validator.
     *
     * As of Symfony 2.3, the first parameter of this method will be typed
     * against {@link MetadataFactoryInterface}.
     *
     * @param MetadataFactoryInterface|Mapping_ClassMetadataFactoryInterface $metadataFactory The metadata factory.
     *
     * @return Symfony_Component_Validator_ValidatorBuilderInterface The builder object.
     */
    public function setMetadataFactory($metadataFactory);

    /**
     * Sets the cache for caching class metadata.
     *
     * @param Symfony_Component_Validator_Mapping_Cache_CacheInterface $cache The cache instance.
     *
     * @return Symfony_Component_Validator_ValidatorBuilderInterface The builder object.
     */
    public function setMetadataCache(Symfony_Component_Validator_Mapping_Cache_CacheInterface $cache);

    /**
     * Sets the constraint validator factory used by the validator.
     *
     * @param Symfony_Component_Validator_ConstraintValidatorFactoryInterface $validatorFactory The validator factory.
     *
     * @return Symfony_Component_Validator_ValidatorBuilderInterface The builder object.
     */
    public function setConstraintValidatorFactory(Symfony_Component_Validator_ConstraintValidatorFactoryInterface $validatorFactory);

    /**
     * Sets the translator used for translating violation messages.
     *
     * @param Symfony_Component_Translation_TranslatorInterface $translator The translator instance.
     *
     * @return Symfony_Component_Validator_ValidatorBuilderInterface The builder object.
     */
    public function setTranslator(Symfony_Component_Translation_TranslatorInterface $translator);

    /**
     * Sets the default translation domain of violation messages.
     *
     * The same message can have different translations in different domains.
     * Pass the domain that is used for violation messages by default to this
     * method.
     *
     * @param string $translationDomain The translation domain of the violation messages.
     *
     * @return Symfony_Component_Validator_ValidatorBuilderInterface The builder object.
     */
    public function setTranslationDomain($translationDomain);

    /**
     * Builds and returns a new validator object.
     *
     * @return Symfony_Component_Validator_ValidatorInterface The built validator.
     */
    public function getValidator();
}
