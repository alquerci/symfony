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

class Symfony_Component_Validator_Tests_GraphWalkerTest extends PHPUnit_Framework_TestCase
{
    const CLASSNAME = 'Symfony_Component_Validator_Tests_Fixtures_Entity';

    /**
     * @var Symfony_Component_Validator_ValidationVisitor
     */
    private $visitor;

    /**
     * @var Symfony_Component_Validator_Tests_Fixtures_FakeMetadataFactory
     */
    protected $metadataFactory;

    /**
     * @var Symfony_Component_Validator_GraphWalker
     */
    protected $walker;

    /**
     * @var Symfony_Component_Validator_Mapping_ClassMetadata
     */
    protected $metadata;

    protected function setUp()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $this->metadataFactory = new Symfony_Component_Validator_Tests_Fixtures_FakeMetadataFactory();
        $this->visitor = new Symfony_Component_Validator_ValidationVisitor('Root', $this->metadataFactory, new Symfony_Component_Validator_ConstraintValidatorFactory(), new Symfony_Component_Validator_DefaultTranslator());
        $this->walker = $this->visitor->getGraphWalker();
        $this->metadata = new Symfony_Component_Validator_Mapping_ClassMetadata(self::CLASSNAME);
        $this->metadataFactory->addMetadata($this->metadata);
    }

    protected function tearDown()
    {
        restore_error_handler();

        $this->metadataFactory = null;
        $this->visitor = null;
        $this->walker = null;
        $this->metadata = null;
    }

    public function deprecationErrorHandler($errorNumber, $message, $file, $line, $context)
    {
        if ($errorNumber & E_USER_DEPRECATED) {
            return true;
        }

        return PHPUnit_Util_ErrorHandler::handleError($errorNumber, $message, $file, $line);
    }

    public function testWalkObjectPassesCorrectClassAndProperty()
    {
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $this->walker->walkObject($this->metadata, $entity, 'Default', '');

        $context = Symfony_Component_Validator_Tests_Fixtures_ConstraintAValidator::$passedContext;

        $this->assertEquals('Symfony_Component_Validator_Tests_Fixtures_Entity', $context->getCurrentClass());
        $this->assertNull($context->getCurrentProperty());
    }

    public function testWalkObjectValidatesConstraints()
    {
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->walker->walkObject($this->metadata, new Symfony_Component_Validator_Tests_Fixtures_Entity(), 'Default', '');

        $this->assertCount(1, $this->walker->getViolations());
    }

    public function testWalkObjectTwiceValidatesConstraintsOnce()
    {
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();

        $this->walker->walkObject($this->metadata, $entity, 'Default', '');
        $this->walker->walkObject($this->metadata, $entity, 'Default', '');

        $this->assertCount(1, $this->walker->getViolations());
    }

    public function testWalkObjectOnceInVisitorAndOnceInWalkerValidatesConstraintsOnce()
    {
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();

        $this->visitor->validate($entity, 'Default', '');
        $this->walker->walkObject($this->metadata, $entity, 'Default', '');

        $this->assertCount(1, $this->walker->getViolations());
    }

    public function testWalkDifferentObjectsValidatesTwice()
    {
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->walker->walkObject($this->metadata, new Symfony_Component_Validator_Tests_Fixtures_Entity(), 'Default', '');
        $this->walker->walkObject($this->metadata, new Symfony_Component_Validator_Tests_Fixtures_Entity(), 'Default', '');

        $this->assertCount(2, $this->walker->getViolations());
    }

    public function testWalkObjectTwiceInDifferentGroupsValidatesTwice()
    {
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => 'Custom')));

        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();

        $this->walker->walkObject($this->metadata, $entity, 'Default', '');
        $this->walker->walkObject($this->metadata, $entity, 'Custom', '');

        $this->assertCount(2, $this->walker->getViolations());
    }

    public function testWalkObjectValidatesPropertyConstraints()
    {
        $this->metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->walker->walkObject($this->metadata, new Symfony_Component_Validator_Tests_Fixtures_Entity(), 'Default', '');

        $this->assertCount(1, $this->walker->getViolations());
    }

    public function testWalkObjectValidatesGetterConstraints()
    {
        $this->metadata->addGetterConstraint('lastName', new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->walker->walkObject($this->metadata, new Symfony_Component_Validator_Tests_Fixtures_Entity(), 'Default', '');

        $this->assertCount(1, $this->walker->getViolations());
    }

    public function testWalkObjectInDefaultGroupTraversesGroupSequence()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();

        $this->metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint(array(
            'groups' => 'First',
        )));
        $this->metadata->addGetterConstraint('lastName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint(array(
            'groups' => 'Default',
        )));
        $this->metadata->setGroupSequence(array('First', $this->metadata->getDefaultGroup()));

        $this->walker->walkObject($this->metadata, $entity, 'Default', '');

        // After validation of group "First" failed, no more group was
        // validated
        $violations = new Symfony_Component_Validator_ConstraintViolationList();
        $violations->add(new Symfony_Component_Validator_ConstraintViolation(
            'Failed',
            'Failed',
            array(),
            'Root',
            'firstName',
            ''
        ));

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkObjectInGroupSequencePropagatesDefaultGroup()
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

        $this->walker->walkObject($this->metadata, $entity, 'Default', '');

        // The validation of the reference's FailingConstraint in group
        // "Default" was launched
        $violations = new Symfony_Component_Validator_ConstraintViolationList();
        $violations->add(new Symfony_Component_Validator_ConstraintViolation(
            'Failed',
            'Failed',
            array(),
            'Root',
            'reference',
            $entity->reference
        ));

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkObjectInOtherGroupTraversesNoGroupSequence()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();

        $this->metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint(array(
            'groups' => 'First',
        )));
        $this->metadata->addGetterConstraint('lastName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint(array(
            'groups' => $this->metadata->getDefaultGroup(),
        )));
        $this->metadata->setGroupSequence(array('First', $this->metadata->getDefaultGroup()));

        $this->walker->walkObject($this->metadata, $entity, $this->metadata->getDefaultGroup(), '');

        // Only group "Second" was validated
        $violations = new Symfony_Component_Validator_ConstraintViolationList();
        $violations->add(new Symfony_Component_Validator_ConstraintViolation(
            'Failed',
            'Failed',
            array(),
            'Root',
            'lastName',
            ''
        ));

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkPropertyPassesCorrectClassAndProperty()
    {
        $this->metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->walker->walkPropertyValue($this->metadata, 'firstName', 'value', 'Default', '');

        $context = Symfony_Component_Validator_Tests_Fixtures_ConstraintAValidator::$passedContext;

        $this->assertEquals('Symfony_Component_Validator_Tests_Fixtures_Entity', $context->getCurrentClass());
        $this->assertEquals('firstName', $context->getCurrentProperty());
    }

    public function testWalkPropertyValueValidatesConstraints()
    {
        $this->metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->walker->walkPropertyValue($this->metadata, 'firstName', 'value', 'Default', '');

        $this->assertCount(1, $this->walker->getViolations());
    }

    public function testWalkCascadedPropertyValidatesReferences()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entityMetadata = new Symfony_Component_Validator_Mapping_ClassMetadata(get_class($entity));
        $this->metadataFactory->addMetadata($entityMetadata);

        // add a constraint for the entity that always fails
        $entityMetadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());

        // validate entity when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());

        // invoke validation on an object
        $this->walker->walkPropertyValue(
            $this->metadata,
            'reference',
            $entity,  // object!
            'Default',
            'path'
        );

        $violations = new Symfony_Component_Validator_ConstraintViolationList();
        $violations->add(new Symfony_Component_Validator_ConstraintViolation(
            'Failed',
            'Failed',
            array(),
            'Root',
            'path',
            $entity
        ));

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkCascadedPropertyValidatesArraysByDefault()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entityMetadata = new Symfony_Component_Validator_Mapping_ClassMetadata(get_class($entity));
        $this->metadataFactory->addMetadata($entityMetadata);

        // add a constraint for the entity that always fails
        $entityMetadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());

        // validate array when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());

        $this->walker->walkPropertyValue(
            $this->metadata,
            'reference',
            array('key' => $entity), // array!
            'Default',
            'path'
        );

        $violations = new Symfony_Component_Validator_ConstraintViolationList();
        $violations->add(new Symfony_Component_Validator_ConstraintViolation(
            'Failed',
            'Failed',
            array(),
            'Root',
            'path[key]',
            $entity
        ));

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkCascadedPropertyValidatesTraversableByDefault()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entityMetadata = new Symfony_Component_Validator_Mapping_ClassMetadata(get_class($entity));
        $this->metadataFactory->addMetadata($entityMetadata);
        $this->metadataFactory->addMetadata(new Symfony_Component_Validator_Mapping_ClassMetadata('ArrayIterator'));

        // add a constraint for the entity that always fails
        $entityMetadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());

        // validate array when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());

        $this->walker->walkPropertyValue(
            $this->metadata,
            'reference',
            new ArrayIterator(array('key' => $entity)),
            'Default',
            'path'
        );

        $violations = new Symfony_Component_Validator_ConstraintViolationList();
        $violations->add(new Symfony_Component_Validator_ConstraintViolation(
            'Failed',
            'Failed',
            array(),
            'Root',
            'path[key]',
            $entity
        ));

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkCascadedPropertyDoesNotValidateTraversableIfDisabled()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entityMetadata = new Symfony_Component_Validator_Mapping_ClassMetadata(get_class($entity));
        $this->metadataFactory->addMetadata($entityMetadata);
        $this->metadataFactory->addMetadata(new Symfony_Component_Validator_Mapping_ClassMetadata('ArrayIterator'));

        // add a constraint for the entity that always fails
        $entityMetadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());

        // validate array when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid(array(
            'traverse' => false,
        )));

        $this->walker->walkPropertyValue(
            $this->metadata,
            'reference',
            new ArrayIterator(array('key' => $entity)),
            'Default',
            'path'
        );

        $violations = new Symfony_Component_Validator_ConstraintViolationList();

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkCascadedPropertyDoesNotRecurseByDefault()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entityMetadata = new Symfony_Component_Validator_Mapping_ClassMetadata(get_class($entity));
        $this->metadataFactory->addMetadata($entityMetadata);
        $this->metadataFactory->addMetadata(new Symfony_Component_Validator_Mapping_ClassMetadata('ArrayIterator'));

        // add a constraint for the entity that always fails
        $entityMetadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());

        // validate iterator when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());

        $this->walker->walkPropertyValue(
            $this->metadata,
            'reference',
            new ArrayIterator(array(
                // The inner iterator should not be traversed by default
                'key' => new ArrayIterator(array(
                    'nested' => $entity,
                )),
            )),
            'Default',
            'path'
        );

        $violations = new Symfony_Component_Validator_ConstraintViolationList();

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkCascadedPropertyRecursesIfDeepIsSet()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $entityMetadata = new Symfony_Component_Validator_Mapping_ClassMetadata(get_class($entity));
        $this->metadataFactory->addMetadata($entityMetadata);
        $this->metadataFactory->addMetadata(new Symfony_Component_Validator_Mapping_ClassMetadata('ArrayIterator'));

        // add a constraint for the entity that always fails
        $entityMetadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());

        // validate iterator when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid(array(
            'deep' => true,
        )));

        $this->walker->walkPropertyValue(
            $this->metadata,
            'reference',
            new ArrayIterator(array(
                // The inner iterator should now be traversed
                'key' => new ArrayIterator(array(
                    'nested' => $entity,
                )),
            )),
            'Default',
            'path'
        );

        $violations = new Symfony_Component_Validator_ConstraintViolationList();
        $violations->add(new Symfony_Component_Validator_ConstraintViolation(
            'Failed',
            'Failed',
            array(),
            'Root',
            'path[key][nested]',
            $entity
        ));

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkCascadedPropertyDoesNotValidateNestedScalarValues()
    {
        // validate array when validating the property "reference"
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());

        $this->walker->walkPropertyValue(
            $this->metadata,
            'reference',
            array('scalar', 'values'),
            'Default',
            'path'
        );

        $violations = new Symfony_Component_Validator_ConstraintViolationList();

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkCascadedPropertyDoesNotValidateNullValues()
    {
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());

        $this->walker->walkPropertyValue(
            $this->metadata,
            'reference',
            null,
            'Default',
            ''
        );

        $this->assertCount(0, $this->walker->getViolations());
    }

    public function testWalkCascadedPropertyRequiresObjectOrArray()
    {
        $this->metadata->addPropertyConstraint('reference', new Symfony_Component_Validator_Constraints_Valid());

        $this->setExpectedException('Symfony_Component_Validator_Exception_NoSuchMetadataException');

        $this->walker->walkPropertyValue(
            $this->metadata,
            'reference',
            'no object',
            'Default',
            ''
        );
    }

    public function testWalkConstraintBuildsAViolationIfFailed()
    {
        $constraint = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA();

        $this->walker->walkConstraint($constraint, 'foobar', 'Default', 'firstName.path');

        $violations = new Symfony_Component_Validator_ConstraintViolationList();
        $violations->add(new Symfony_Component_Validator_ConstraintViolation(
            'message',
            'message',
            array('param' => 'value'),
            'Root',
            'firstName.path',
            'foobar'
        ));

        $this->assertEquals($violations, $this->walker->getViolations());
    }

    public function testWalkConstraintBuildsNoViolationIfSuccessful()
    {
        $constraint = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA();

        $this->walker->walkConstraint($constraint, 'VALID', 'Default', 'firstName.path');

        $this->assertCount(0, $this->walker->getViolations());
    }

    public function testWalkObjectUsesCorrectPropertyPathInViolationsWhenUsingCollections()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Collection(array(
            'foo' => new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(),
            'bar' => new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(),
        ));

        $this->walker->walkConstraint($constraint, array('foo' => 'VALID'), 'Default', 'collection');
        $violations = $this->walker->getViolations();
        $this->assertEquals('collection[bar]', $violations[0]->getPropertyPath());
    }

    public function testWalkObjectUsesCorrectPropertyPathInViolationsWhenUsingNestedCollections()
    {
        $constraint = new Symfony_Component_Validator_Constraints_Collection(array(
            'foo' => new Symfony_Component_Validator_Constraints_Collection(array(
                'foo' => new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(),
                'bar' => new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(),
            )),
        ));

        $this->walker->walkConstraint($constraint, array('foo' => array('foo' => 'VALID')), 'Default', 'collection');
        $violations = $this->walker->getViolations();
        $this->assertEquals('collection[foo][bar]', $violations[0]->getPropertyPath());
    }

    protected function getProperty($property)
    {
        $p = new ReflectionProperty($this->walker, $property);
        $p->setAccessible(true);

        return $p->getValue($this->walker);
    }
}
