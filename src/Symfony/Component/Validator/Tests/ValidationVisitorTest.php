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
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Validator_Tests_ValidationVisitorTest extends PHPUnit_Framework_TestCase
{
    const CLASS_NAME = 'Symfony_Component_Validator_Tests_Fixtures_Entity';

    /**
     * @var Symfony_Component_Validator_ValidationVisitor
     */
    private $visitor;

    /**
     * @var Symfony_Component_Validator_Tests_Fixtures_FakeMetadataFactory
     */
    private $metadataFactory;

    /**
     * @var Symfony_Component_Validator_Mapping_ClassMetadata
     */
    private $metadata;

    protected function setUp()
    {
        $this->metadataFactory = new Symfony_Component_Validator_Tests_Fixtures_FakeMetadataFactory();
        $this->visitor = new Symfony_Component_Validator_ValidationVisitor('Root', $this->metadataFactory, new Symfony_Component_Validator_ConstraintValidatorFactory(), new Symfony_Component_Validator_DefaultTranslator());
        $this->metadata = new Symfony_Component_Validator_Mapping_ClassMetadata(self::CLASS_NAME);
        $this->metadataFactory->addMetadata($this->metadata);
    }

    protected function tearDown()
    {
        $this->metadataFactory = null;
        $this->visitor = null;
        $this->metadata = null;
    }

    public function testValidatePassesCorrectClassAndProperty()
    {
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $this->visitor->validate($entity, 'Default', '');

        $context = Symfony_Component_Validator_Tests_Fixtures_ConstraintAValidator::$passedContext;

        $this->assertEquals('Symfony_Component_Validator_Tests_Fixtures_Entity', $context->getClassName());
        $this->assertNull($context->getPropertyName());
    }

    public function testValidateConstraints()
    {
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->visitor->validate(new Symfony_Component_Validator_Tests_Fixtures_Entity(), 'Default', '');

        $this->assertCount(1, $this->visitor->getViolations());
    }

    public function testValidateTwiceValidatesConstraintsOnce()
    {
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();

        $this->visitor->validate($entity, 'Default', '');
        $this->visitor->validate($entity, 'Default', '');

        $this->assertCount(1, $this->visitor->getViolations());
    }

    public function testValidateDifferentObjectsValidatesTwice()
    {
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->visitor->validate(new Symfony_Component_Validator_Tests_Fixtures_Entity(), 'Default', '');
        $this->visitor->validate(new Symfony_Component_Validator_Tests_Fixtures_Entity(), 'Default', '');

        $this->assertCount(2, $this->visitor->getViolations());
    }

    public function testValidateTwiceInDifferentGroupsValidatesTwice()
    {
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => 'Custom')));

        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();

        $this->visitor->validate($entity, 'Default', '');
        $this->visitor->validate($entity, 'Custom', '');

        $this->assertCount(2, $this->visitor->getViolations());
    }

    public function testValidatePropertyConstraints()
    {
        $this->metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->visitor->validate(new Symfony_Component_Validator_Tests_Fixtures_Entity(), 'Default', '');

        $this->assertCount(1, $this->visitor->getViolations());
    }

    public function testValidateGetterConstraints()
    {
        $this->metadata->addGetterConstraint('lastName', new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->visitor->validate(new Symfony_Component_Validator_Tests_Fixtures_Entity(), 'Default', '');

        $this->assertCount(1, $this->visitor->getViolations());
    }

    public function testValidateInDefaultGroupTraversesGroupSequence()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();

        $this->metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint(array(
            'groups' => 'First',
        )));
        $this->metadata->addGetterConstraint('lastName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint(array(
            'groups' => 'Default',
        )));
        $this->metadata->setGroupSequence(array('First', $this->metadata->getDefaultGroup()));

        $this->visitor->validate($entity, 'Default', '');

        // After validation of group "First" failed, no more group was
        // validated
        $violations = new Symfony_Component_Validator_ConstraintViolationList(array(
            new Symfony_Component_Validator_ConstraintViolation(
                'Failed',
                'Failed',
                array(),
                'Root',
                'firstName',
                ''
            ),
        ));

        $this->assertEquals($violations, $this->visitor->getViolations());
    }

    public function testValidateInGroupSequencePropagatesDefaultGroup()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entity->reference = new Symfony_Component_Validator_Tests_Fixtures_Reference();

        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());
        $this->metadata->setGroupSequence(array($this->metadata->getDefaultGroup()));

        $referenceMetadata = new Symfony_Component_Validator_Mapping_ClassMetadata(get_class($entity->reference));
        $referenceMetadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint(array(
                // this constraint is only evaluated if group "Default" is
                // propagated to the reference
                'groups' => 'Default',
            )));
        $this->metadataFactory->addMetadata($referenceMetadata);

        $this->visitor->validate($entity, 'Default', '');

        // The validation of the reference's FailingConstraint in group
        // "Default" was launched
        $violations = new Symfony_Component_Validator_ConstraintViolationList(array(
            new Symfony_Component_Validator_ConstraintViolation(
                'Failed',
                'Failed',
                array(),
                'Root',
                'reference',
                $entity->reference
            ),
        ));

        $this->assertEquals($violations, $this->visitor->getViolations());
    }

    public function testValidateInOtherGroupTraversesNoGroupSequence()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();

        $this->metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint(array(
            'groups' => 'First',
        )));
        $this->metadata->addGetterConstraint('lastName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint(array(
            'groups' => $this->metadata->getDefaultGroup(),
        )));
        $this->metadata->setGroupSequence(array('First', $this->metadata->getDefaultGroup()));

        $this->visitor->validate($entity, $this->metadata->getDefaultGroup(), '');

        // Only group "Second" was validated
        $violations = new Symfony_Component_Validator_ConstraintViolationList(array(
            new Symfony_Component_Validator_ConstraintViolation(
                'Failed',
                'Failed',
                array(),
                'Root',
                'lastName',
                ''
            ),
        ));

        $this->assertEquals($violations, $this->visitor->getViolations());
    }

    public function testValidateCascadedPropertyValidatesReferences()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entity->reference = new Symfony_Component_Validator_Tests_Fixtures_Entity();

        // add a constraint for the entity that always fails
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());

        // validate entity when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());

        // invoke validation on an object
        $this->visitor->validate($entity, 'Default', '');

        $violations = new Symfony_Component_Validator_ConstraintViolationList(array(
            // generated by the root object
            new Symfony_Component_Validator_ConstraintViolation(
                'Failed',
                'Failed',
                array(),
                'Root',
                '',
                $entity
            ),
            // generated by the reference
            new Symfony_Component_Validator_ConstraintViolation(
                'Failed',
                'Failed',
                array(),
                'Root',
                'reference',
                $entity->reference
            ),
        ));

        $this->assertEquals($violations, $this->visitor->getViolations());
    }

    public function testValidateCascadedPropertyValidatesArraysByDefault()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entity->reference = array('key' => new Symfony_Component_Validator_Tests_Fixtures_Entity());

        // add a constraint for the entity that always fails
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());

        // validate array when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());

        $this->visitor->validate($entity, 'Default', '');

        $violations = new Symfony_Component_Validator_ConstraintViolationList(array(
            // generated by the root object
            new Symfony_Component_Validator_ConstraintViolation(
                'Failed',
                'Failed',
                array(),
                'Root',
                '',
                $entity
            ),
            // generated by the reference
            new Symfony_Component_Validator_ConstraintViolation(
                'Failed',
                'Failed',
                array(),
                'Root',
                'reference[key]',
                $entity->reference['key']
            ),
        ));

        $this->assertEquals($violations, $this->visitor->getViolations());
    }

    public function testValidateCascadedPropertyValidatesTraversableByDefault()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entity->reference = new ArrayIterator(array('key' => new Symfony_Component_Validator_Tests_Fixtures_Entity()));

        // add a constraint for the entity that always fails
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());

        // validate array when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());

        $this->visitor->validate($entity, 'Default', '');

        $violations = new Symfony_Component_Validator_ConstraintViolationList(array(
            // generated by the root object
            new Symfony_Component_Validator_ConstraintViolation(
                'Failed',
                'Failed',
                array(),
                'Root',
                '',
                $entity
            ),
            // generated by the reference
            new Symfony_Component_Validator_ConstraintViolation(
                'Failed',
                'Failed',
                array(),
                'Root',
                'reference[key]',
                $entity->reference['key']
            ),
        ));

        $this->assertEquals($violations, $this->visitor->getViolations());
    }

    public function testValidateCascadedPropertyDoesNotValidateTraversableIfDisabled()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entity->reference = new ArrayIterator(array('key' => new Symfony_Component_Validator_Tests_Fixtures_Entity()));

        $this->metadataFactory->addMetadata(new Symfony_Component_Validator_Mapping_ClassMetadata('ArrayIterator'));

        // add a constraint for the entity that always fails
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());

        // validate array when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid(array(
            'traverse' => false,
        )));

        $this->visitor->validate($entity, 'Default', '');

        $violations = new Symfony_Component_Validator_ConstraintViolationList(array(
            // generated by the root object
            new Symfony_Component_Validator_ConstraintViolation(
                'Failed',
                'Failed',
                array(),
                'Root',
                '',
                $entity
            ),
            // nothing generated by the reference!
        ));

        $this->assertEquals($violations, $this->visitor->getViolations());
    }

    public function testMetadataMayNotExistIfTraversalIsEnabled()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entity->reference = new ArrayIterator();

        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid(array(
            'traverse' => true,
        )));

        $this->visitor->validate($entity, 'Default', '');
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_NoSuchMetadataException
     */
    public function testMetadataMustExistIfTraversalIsDisabled()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entity->reference = new ArrayIterator();

        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid(array(
            'traverse' => false,
        )));

        $this->visitor->validate($entity, 'Default', '');
    }

    public function testValidateCascadedPropertyDoesNotRecurseByDefault()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entity->reference = new ArrayIterator(array(
            // The inner iterator should not be traversed by default
            'key' => new ArrayIterator(array(
                'nested' => new Symfony_Component_Validator_Tests_Fixtures_Entity(),
            )),
        ));

        $this->metadataFactory->addMetadata(new Symfony_Component_Validator_Mapping_ClassMetadata('ArrayIterator'));

        // add a constraint for the entity that always fails
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());

        // validate iterator when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());

        $this->visitor->validate($entity, 'Default', '');

        $violations = new Symfony_Component_Validator_ConstraintViolationList(array(
            // generated by the root object
            new Symfony_Component_Validator_ConstraintViolation(
                'Failed',
                'Failed',
                array(),
                'Root',
                '',
                $entity
            ),
            // nothing generated by the reference!
        ));

        $this->assertEquals($violations, $this->visitor->getViolations());
    }

    // https://github.com/symfony/symfony/issues/6246
    public function testValidateCascadedPropertyRecursesArraysByDefault()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entity->reference = array(
            'key' => array(
                'nested' => new Symfony_Component_Validator_Tests_Fixtures_Entity(),
            ),
        );

        // add a constraint for the entity that always fails
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());

        // validate iterator when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());

        $this->visitor->validate($entity, 'Default', '');

        $violations = new Symfony_Component_Validator_ConstraintViolationList(array(
            // generated by the root object
            new Symfony_Component_Validator_ConstraintViolation(
                'Failed',
                'Failed',
                array(),
                'Root',
                '',
                $entity
            ),
            // nothing generated by the reference!
            new Symfony_Component_Validator_ConstraintViolation(
                'Failed',
                'Failed',
                array(),
                'Root',
                'reference[key][nested]',
                $entity->reference['key']['nested']
            ),
        ));

        $this->assertEquals($violations, $this->visitor->getViolations());
    }

    public function testValidateCascadedPropertyRecursesIfDeepIsSet()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entity->reference = new ArrayIterator(array(
            // The inner iterator should now be traversed
            'key' => new ArrayIterator(array(
                'nested' => new Symfony_Component_Validator_Tests_Fixtures_Entity(),
            )),
        ));

        // add a constraint for the entity that always fails
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());

        // validate iterator when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid(array(
            'deep' => true,
        )));

        $this->visitor->validate($entity, 'Default', '');

        $violations = new Symfony_Component_Validator_ConstraintViolationList(array(
            // generated by the root object
            new Symfony_Component_Validator_ConstraintViolation(
                'Failed',
                'Failed',
                array(),
                'Root',
                '',
                $entity
            ),
            // nothing generated by the reference!
            new Symfony_Component_Validator_ConstraintViolation(
                'Failed',
                'Failed',
                array(),
                'Root',
                'reference[key][nested]',
                $entity->reference['key']['nested']
            ),
        ));

        $this->assertEquals($violations, $this->visitor->getViolations());
    }

    public function testValidateCascadedPropertyDoesNotValidateNestedScalarValues()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entity->reference = array('scalar', 'values');

        // validate array when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());

        $this->visitor->validate($entity, 'Default', '');

        $this->assertCount(0, $this->visitor->getViolations());
    }

    public function testValidateCascadedPropertyDoesNotValidateNullValues()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entity->reference = null;

        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());

        $this->visitor->validate($entity, 'Default', '');

        $this->assertCount(0, $this->visitor->getViolations());
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_NoSuchMetadataException
     */
    public function testValidateCascadedPropertyRequiresObjectOrArray()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entity->reference = 'no object';

        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());

        $this->visitor->validate($entity, 'Default', '');
    }
}
