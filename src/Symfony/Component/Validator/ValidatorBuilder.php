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
 * The default implementation of {@link ValidatorBuilderInterface}.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Validator_ValidatorBuilder implements Symfony_Component_Validator_ValidatorBuilderInterface
{
    /**
     * @var array
     */
    private $initializers = array();

    /**
     * @var array
     */
    private $xmlMappings = array();

    /**
     * @var array
     */
    private $yamlMappings = array();

    /**
     * @var array
     */
    private $methodMappings = array();

    /**
     * @var Doctrine_Common_Annotations_Reader
     */
    private $annotationReader = null;

    /**
     * @var Symfony_Component_Validator_MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var Symfony_Component_Validator_ConstraintValidatorFactoryInterface
     */
    private $validatorFactory;

    /**
     * @var Symfony_Component_Validator_Mapping_Cache_CacheInterface
     */
    private $metadataCache;

    /**
     * @var Symfony_Component_Translation_TranslatorInterface
     */
    private $translator;

    /**
     * @var null|string
     */
    private $translationDomain;

    /**
     * {@inheritdoc}
     */
    public function addObjectInitializer(Symfony_Component_Validator_ObjectInitializerInterface $initializer)
    {
        $this->initializers[] = $initializer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addObjectInitializers(array $initializers)
    {
        $this->initializers = array_merge($this->initializers, $initializers);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addXmlMapping($path)
    {
        if (null !== $this->metadataFactory) {
            throw new Symfony_Component_Validator_Exception_ValidatorException('You cannot add custom mappings after setting a custom metadata factory. Configure your metadata factory instead.');
        }

        $this->xmlMappings[] = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addXmlMappings(array $paths)
    {
        if (null !== $this->metadataFactory) {
            throw new Symfony_Component_Validator_Exception_ValidatorException('You cannot add custom mappings after setting a custom metadata factory. Configure your metadata factory instead.');
        }

        $this->xmlMappings = array_merge($this->xmlMappings, $paths);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addYamlMapping($path)
    {
        if (null !== $this->metadataFactory) {
            throw new Symfony_Component_Validator_Exception_ValidatorException('You cannot add custom mappings after setting a custom metadata factory. Configure your metadata factory instead.');
        }

        $this->yamlMappings[] = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addYamlMappings(array $paths)
    {
        if (null !== $this->metadataFactory) {
            throw new Symfony_Component_Validator_Exception_ValidatorException('You cannot add custom mappings after setting a custom metadata factory. Configure your metadata factory instead.');
        }

        $this->yamlMappings = array_merge($this->yamlMappings, $paths);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMethodMapping($methodName)
    {
        if (null !== $this->metadataFactory) {
            throw new Symfony_Component_Validator_Exception_ValidatorException('You cannot add custom mappings after setting a custom metadata factory. Configure your metadata factory instead.');
        }

        $this->methodMappings[] = $methodName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMethodMappings(array $methodNames)
    {
        if (null !== $this->metadataFactory) {
            throw new Symfony_Component_Validator_Exception_ValidatorException('You cannot add custom mappings after setting a custom metadata factory. Configure your metadata factory instead.');
        }

        $this->methodMappings = array_merge($this->methodMappings, $methodNames);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function enableAnnotationMapping(Doctrine_Common_Annotations_Reader $annotationReader = null)
    {
        if (null !== $this->metadataFactory) {
            throw new Symfony_Component_Validator_Exception_ValidatorException('You cannot enable annotation mapping after setting a custom metadata factory. Configure your metadata factory instead.');
        }

        if (null === $annotationReader) {
            if (!class_exists('Doctrine_Common_Annotations_AnnotationReader')) {
                throw new RuntimeException('Requested a ValidatorFactory with an AnnotationLoader, but the AnnotationReader was not found. You should add Doctrine Common to your project.');
            }

            $annotationReader = new Doctrine_Common_Annotations_CachedReader(new Doctrine_Common_Annotations_AnnotationReader(), new Doctrine_Common_Cache_ArrayCache());
        }

        $this->annotationReader = $annotationReader;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function disableAnnotationMapping()
    {
        $this->annotationReader = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetadataFactory($metadataFactory)
    {
        if (count($this->xmlMappings) > 0 || count($this->yamlMappings) > 0 || count($this->methodMappings) > 0 || null !== $this->annotationReader) {
            throw new Symfony_Component_Validator_Exception_ValidatorException('You cannot set a custom metadata factory after adding custom mappings. You should do either of both.');
        }

        if ($metadataFactory instanceof Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface
                && !$metadataFactory instanceof Symfony_Component_Validator_MetadataFactoryInterface) {
            $metadataFactory = new Symfony_Component_Validator_Mapping_ClassMetadataFactoryAdapter($metadataFactory);
        }

        $this->metadataFactory = $metadataFactory;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetadataCache(Symfony_Component_Validator_Mapping_Cache_CacheInterface $cache)
    {
        if (null !== $this->metadataFactory) {
            throw new Symfony_Component_Validator_Exception_ValidatorException('You cannot set a custom metadata cache after setting a custom metadata factory. Configure your metadata factory instead.');
        }

        $this->metadataCache = $cache;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConstraintValidatorFactory(Symfony_Component_Validator_ConstraintValidatorFactoryInterface $validatorFactory)
    {
        $this->validatorFactory = $validatorFactory;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTranslator(Symfony_Component_Translation_TranslatorInterface $translator)
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTranslationDomain($translationDomain)
    {
        $this->translationDomain = $translationDomain;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidator()
    {
        $metadataFactory = $this->metadataFactory;

        if (!$metadataFactory) {
            $loaders = array();

            if (count($this->xmlMappings) > 1) {
                $loaders[] = new Symfony_Component_Validator_Mapping_Loader_XmlFilesLoader($this->xmlMappings);
            } elseif (1 === count($this->xmlMappings)) {
                $loaders[] = new Symfony_Component_Validator_Mapping_Loader_XmlFileLoader($this->xmlMappings[0]);
            }

            if (count($this->yamlMappings) > 1) {
                $loaders[] = new Symfony_Component_Validator_Mapping_Loader_YamlFilesLoader($this->yamlMappings);
            } elseif (1 === count($this->yamlMappings)) {
                $loaders[] = new Symfony_Component_Validator_Mapping_Loader_YamlFileLoader($this->yamlMappings[0]);
            }

            foreach ($this->methodMappings as $methodName) {
                $loaders[] = new Symfony_Component_Validator_Mapping_Loader_StaticMethodLoader($methodName);
            }

            if ($this->annotationReader) {
                $loaders[] = new Symfony_Component_Validator_Mapping_Loader_AnnotationLoader($this->annotationReader);
            }

            $loader = null;

            if (count($loaders) > 1) {
                $loader = new Symfony_Component_Validator_Mapping_Loader_LoaderChain($loaders);
            } elseif (1 === count($loaders)) {
                $loader = $loaders[0];
            }

            $metadataFactory = new Symfony_Component_Validator_Mapping_ClassMetadataFactory($loader, $this->metadataCache);
        }

        $validatorFactory = $this->validatorFactory ?: new Symfony_Component_Validator_ConstraintValidatorFactory();
        $translator = $this->translator ?: new Symfony_Component_Validator_DefaultTranslator();

        return new Symfony_Component_Validator_Validator($metadataFactory, $validatorFactory, $translator, $this->translationDomain, $this->initializers);
    }
}
