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
 * Responsible for walking over and initializing validation on different
 * types of items.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @deprecated Deprecated since version 2.2, to be removed in 2.3. This class
 *             has been replaced by {@link ValidationVisitorInterface} and
 *             {@link MetadataInterface}.
 */
class Symfony_Component_Validator_GraphWalker
{
    /**
     * @var Symfony_Component_Validator_ValidationVisitor
     */
    private $visitor;

    /**
     * @var Symfony_Component_Validator_MetadataFactoryInterface
     */
    private $metadataFactory;

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
    private $validatedObjects;

    /**
     * Creates a new graph walker.
     *
     * @param Symfony_Component_Validator_ValidationVisitor        $visitor
     * @param Symfony_Component_Validator_MetadataFactoryInterface $metadataFactory
     * @param Symfony_Component_Translation_TranslatorInterface      $translator
     * @param null|string              $translationDomain
     * @param array                    $validatedObjects
     *
     * @deprecated Deprecated since version 2.2, to be removed in 2.3.
     */
    public function __construct(Symfony_Component_Validator_ValidationVisitor $visitor, Symfony_Component_Validator_MetadataFactoryInterface $metadataFactory, Symfony_Component_Translation_TranslatorInterface $translator, $translationDomain = null, array &$validatedObjects = array())
    {
        trigger_error('GraphWalker is deprecated since version 2.2 and will be removed in 2.3. This class has been replaced by ValidationVisitorInterface and MetadataInterface.', E_USER_DEPRECATED);

        $this->visitor = $visitor;
        $this->metadataFactory = $metadataFactory;
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
        $this->validatedObjects = &$validatedObjects;
    }

    /**
     * @return Symfony_Component_Validator_ConstraintViolationList
     *
     * @deprecated Deprecated since version 2.2, to be removed in 2.3.
     */
    public function getViolations()
    {
        trigger_error('getViolations() is deprecated since version 2.2 and will be removed in 2.3.', E_USER_DEPRECATED);

        return $this->visitor->getViolations();
    }

    /**
     * Initialize validation on the given object using the given metadata
     * instance and validation group.
     *
     * @param Symfony_Component_Validator_Mapping_ClassMetadata $metadata
     * @param object        $object       The object to validate
     * @param string        $group        The validator group to use for validation
     * @param string        $propertyPath
     *
     * @deprecated Deprecated since version 2.2, to be removed in 2.3.
     */
    public function walkObject(Symfony_Component_Validator_Mapping_ClassMetadata $metadata, $object, $group, $propertyPath)
    {
        trigger_error('walkObject() is deprecated since version 2.2 and will be removed in 2.3.', E_USER_DEPRECATED);

        $hash = spl_object_hash($object);

        // Exit, if the object is already validated for the current group
        if (isset($this->validatedObjects[$hash][$group])) {
            return;
        }

        // Remember validating this object before starting and possibly
        // traversing the object graph
        $this->validatedObjects[$hash][$group] = true;

        $metadata->accept($this->visitor, $object, $group, $propertyPath);
    }

    protected function walkObjectForGroup(Symfony_Component_Validator_Mapping_ClassMetadata $metadata, $object, $group, $propertyPath, $propagatedGroup = null)
    {
        $metadata->accept($this->visitor, $object, $group, $propertyPath, $propagatedGroup);
    }

    /**
     * Validates a property of a class.
     *
     * @param Symfony_Component_Validator_Mapping_ClassMetadata $metadata
     * @param                       $property
     * @param                       $object
     * @param                       $group
     * @param                       $propertyPath
     * @param null                  $propagatedGroup
     *
     * @throws Symfony_Component_Validator_Exception_UnexpectedTypeException
     *
     * @deprecated Deprecated since version 2.2, to be removed in 2.3.
     */
    public function walkProperty(Symfony_Component_Validator_Mapping_ClassMetadata $metadata, $property, $object, $group, $propertyPath, $propagatedGroup = null)
    {
        trigger_error('walkProperty() is deprecated since version 2.2 and will be removed in 2.3.', E_USER_DEPRECATED);

        if (!is_object($object)) {
            throw new Symfony_Component_Validator_Exception_UnexpectedTypeException($object, 'object');
        }

        foreach ($metadata->getMemberMetadatas($property) as $member) {
            $member->accept($this->visitor, $member->getValue($object), $group, $propertyPath, $propagatedGroup);
        }
    }

    /**
     * Validates a property of a class against a potential value.
     *
     * @param Symfony_Component_Validator_Mapping_ClassMetadata $metadata
     * @param                       $property
     * @param                       $value
     * @param                       $group
     * @param                       $propertyPath
     *
     * @deprecated Deprecated since version 2.2, to be removed in 2.3.
     */
    public function walkPropertyValue(Symfony_Component_Validator_Mapping_ClassMetadata $metadata, $property, $value, $group, $propertyPath)
    {
        trigger_error('walkPropertyValue() is deprecated since version 2.2 and will be removed in 2.3.', E_USER_DEPRECATED);

        foreach ($metadata->getMemberMetadatas($property) as $member) {
            $member->accept($this->visitor, $value, $group, $propertyPath);
        }
    }

    protected function walkMember(Symfony_Component_Validator_Mapping_MemberMetadata $metadata, $value, $group, $propertyPath, $propagatedGroup = null)
    {
        $metadata->accept($this->visitor, $value, $group, $propertyPath, $propagatedGroup);
    }

    /**
     * Validates an object or an array.
     *
     * @param      $value
     * @param      $group
     * @param      $propertyPath
     * @param      $traverse
     * @param bool $deep
     *
     * @deprecated Deprecated since version 2.2, to be removed in 2.3.
     */
    public function walkReference($value, $group, $propertyPath, $traverse, $deep = false)
    {
        trigger_error('walkReference() is deprecated since version 2.2 and will be removed in 2.3.', E_USER_DEPRECATED);

        $this->visitor->validate($value, $group, $propertyPath, $traverse, $deep);
    }

    /**
     * Validates a value against a constraint.
     *
     * @param Symfony_Component_Validator_Constraint $constraint
     * @param            $value
     * @param            $group
     * @param            $propertyPath
     * @param null       $currentClass
     * @param null       $currentProperty
     *
     * @deprecated Deprecated since version 2.2, to be removed in 2.3.
     */
    public function walkConstraint(Symfony_Component_Validator_Constraint $constraint, $value, $group, $propertyPath, $currentClass = null, $currentProperty = null)
    {
        trigger_error('walkConstraint() is deprecated since version 2.2 and will be removed in 2.3.', E_USER_DEPRECATED);

        $metadata = null;

        // BC code to make getCurrentClass() and getCurrentProperty() work when
        // called from within this method
        if (null !== $currentClass) {
            $metadata = $this->metadataFactory->getMetadataFor($currentClass);

            if (null !== $currentProperty && $metadata instanceof Symfony_Component_Validator_PropertyMetadataContainerInterface) {
                $metadata = current($metadata->getPropertyMetadata($currentProperty));
            }
        }

        $context = new Symfony_Component_Validator_ExecutionContext(
            $this->visitor,
            $this->translator,
            $this->translationDomain,
            $metadata,
            $value,
            $group,
            $propertyPath
        );

        $context->validateValue($value, $constraint);
    }
}
