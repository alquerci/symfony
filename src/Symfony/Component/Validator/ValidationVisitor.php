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
 * Default implementation of {@link ValidationVisitorInterface} and
 * {@link GlobalExecutionContextInterface}.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Validator_ValidationVisitor implements Symfony_Component_Validator_ValidationVisitorInterface, Symfony_Component_Validator_GlobalExecutionContextInterface
{
    /**
     * @var mixed
     */
    private $root;

    /**
     * @var Symfony_Component_Validator_MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var Symfony_Component_Validator_ConstraintValidatorFactoryInterface
     */
    private $validatorFactory;

    /**
     * @var Symfony_Component_Translation_TranslatorInterface
     */
    private $translator;

    /**
     * @var null|string
     */
    private $translationDomain;

    /**
     * @var array
     */
    private $objectInitializers;

    /**
     * @var Symfony_Component_Validator_ConstraintViolationList
     */
    private $violations;

    /**
     * @var array
     */
    private $validatedObjects = array();

    /**
     * @var Symfony_Component_Validator_GraphWalker
     *
     * @deprecated Deprecated since version 2.2, to be removed in 2.3.
     */
    private $graphWalker;

    /**
     * @var array
     */
    private $objects = array();

    /**
     * Creates a new validation visitor.
     *
     * @param mixed                               $root               The value passed to the validator.
     * @param Symfony_Component_Validator_MetadataFactoryInterface            $metadataFactory    The factory for obtaining metadata instances.
     * @param Symfony_Component_Validator_ConstraintValidatorFactoryInterface $validatorFactory   The factory for creating constraint validators.
     * @param Symfony_Component_Translation_TranslatorInterface                 $translator         The translator for translating violation messages.
     * @param string|null                         $translationDomain  The domain of the translation messages.
     * @param Symfony_Component_Validator_ObjectInitializerInterface[]        $objectInitializers The initializers for preparing objects before validation.
     *
     * @throws Symfony_Component_Validator_Exception_UnexpectedTypeException If any of the object initializers is not an instance of ObjectInitializerInterface
     */
    public function __construct($root, Symfony_Component_Validator_MetadataFactoryInterface $metadataFactory, Symfony_Component_Validator_ConstraintValidatorFactoryInterface $validatorFactory, Symfony_Component_Translation_TranslatorInterface $translator, $translationDomain = null, array $objectInitializers = array())
    {
        foreach ($objectInitializers as $initializer) {
            if (!$initializer instanceof Symfony_Component_Validator_ObjectInitializerInterface) {
                throw new Symfony_Component_Validator_Exception_UnexpectedTypeException($initializer, 'Symfony_Component_Validator_ObjectInitializerInterface');
            }
        }

        $this->root = $root;
        $this->metadataFactory = $metadataFactory;
        $this->validatorFactory = $validatorFactory;
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
        $this->objectInitializers = $objectInitializers;
        $this->violations = new Symfony_Component_Validator_ConstraintViolationList();
    }

    /**
     * {@inheritdoc}
     */
    public function visit(Symfony_Component_Validator_MetadataInterface $metadata, $value, $group, $propertyPath)
    {
        $context = new Symfony_Component_Validator_ExecutionContext(
            $this,
            $this->translator,
            $this->translationDomain,
            $metadata,
            $value,
            $group,
            $propertyPath
        );

        $context->validateValue($value, $metadata->findConstraints($group));
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, $group, $propertyPath, $traverse = false, $deep = false)
    {
        if (null === $value) {
            return;
        }

        if (is_object($value)) {
            $hash = $this->getObjectHash($value);

            // Exit, if the object is already validated for the current group
            if (isset($this->validatedObjects[$hash][$group])) {
                return;
            }

            // Remember validating this object before starting and possibly
            // traversing the object graph
            $this->validatedObjects[$hash][$group] = true;

            foreach ($this->objectInitializers as $initializer) {
                if (!$initializer instanceof Symfony_Component_Validator_ObjectInitializerInterface) {
                    throw new LogicException('Validator initializers must implement ObjectInitializerInterface.');
                }
                $initializer->initialize($value);
            }
        }

        // Validate arrays recursively by default, otherwise every driver needs
        // to implement special handling for arrays.
        // https://github.com/symfony/symfony/issues/6246
        if (is_array($value) || ($traverse && $value instanceof Traversable)) {
            foreach ($value as $key => $element) {
                // Ignore any scalar values in the collection
                if (is_object($element) || is_array($element)) {
                    // Only repeat the traversal if $deep is set
                    $this->validate($element, $group, $propertyPath.'['.$key.']', $deep, $deep);
                }
            }

            try {
                $this->metadataFactory->getMetadataFor($value)->accept($this, $value, $group, $propertyPath);
            } catch (Symfony_Component_Validator_Exception_NoSuchMetadataException $e) {
                // Metadata doesn't necessarily have to exist for
                // traversable objects, because we know how to validate
                // them anyway. Optionally, additional metadata is supported.
            }
        } else {
            $this->metadataFactory->getMetadataFor($value)->accept($this, $value, $group, $propertyPath);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getGraphWalker()
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('getGraphWalker() is deprecated since version 2.2 and will be removed in 2.3.', E_USER_DEPRECATED);

        if (null === $this->graphWalker) {
            $this->graphWalker = new Symfony_Component_Validator_GraphWalker($this, $this->metadataFactory, $this->translator, $this->translationDomain, $this->validatedObjects);
        }

        return $this->graphWalker;
    }

    /**
     * {@inheritdoc}
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * {@inheritdoc}
     */
    public function getVisitor()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidatorFactory()
    {
        return $this->validatorFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFactory()
    {
        return $this->metadataFactory;
    }

    /**
     * Calculate a unique identifier for the specified object.
     *
     * @param object $object The object whose identifier is to be calculated
     *
     * @return string A string with the calculated identifier
     */
    public function getObjectHash($object)
    {
        if (function_exists('spl_object_hash')) {
            return spl_object_hash($object);
        }

        // TODO optimize
        foreach ($this->objects as $hash => $entity) {
            if ($entity === $object) {
                return $hash;
            }
        }

        do {
            $hash = sha1(uniqid(mt_rand(), true));
        } while (isset($this->objects[$hash]));
        $this->objects[$hash] = $object;

        return $hash;
    }
}
