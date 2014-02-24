<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony_Component_Validator_Tests_Fixtures_FakeMetadataFactory
     */
    private $metadataFactory;

    /**
     * @var Symfony_Component_Validator_Validator
     */
    private $validator;

    protected function setUp()
    {
        $this->metadataFactory = new Symfony_Component_Validator_Tests_Fixtures_FakeMetadataFactory();
        $this->validator = new Symfony_Component_Validator_Validator($this->metadataFactory, new Symfony_Component_Validator_ConstraintValidatorFactory(), new Symfony_Component_Validator_DefaultTranslator());
    }

    protected function tearDown()
    {
        $this->metadataFactory = null;
        $this->validator = null;
    }

    public function testValidateDefaultGroup()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata(get_class($entity));
        $metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());
        $metadata->addPropertyConstraint('lastName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint(array(
            'groups' => 'Custom',
        )));
        $this->metadataFactory->addMetadata($metadata);

        // Only the constraint of group "Default" failed
        $violations = new Symfony_Component_Validator_ConstraintViolationList();
        $violations->add(new Symfony_Component_Validator_ConstraintViolation(
            'Failed',
            'Failed',
            array(),
            $entity,
            'firstName',
            ''
        ));

        $this->assertEquals($violations, $this->validator->validate($entity));
    }

    public function testValidateOneGroup()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata(get_class($entity));
        $metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());
        $metadata->addPropertyConstraint('lastName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint(array(
            'groups' => 'Custom',
        )));
        $this->metadataFactory->addMetadata($metadata);

        // Only the constraint of group "Custom" failed
        $violations = new Symfony_Component_Validator_ConstraintViolationList();
        $violations->add(new Symfony_Component_Validator_ConstraintViolation(
            'Failed',
            'Failed',
            array(),
            $entity,
            'lastName',
            ''
        ));

        $this->assertEquals($violations, $this->validator->validate($entity, 'Custom'));
    }

    public function testValidateMultipleGroups()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata(get_class($entity));
        $metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint(array(
            'groups' => 'First',
        )));
        $metadata->addPropertyConstraint('lastName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint(array(
            'groups' => 'Second',
        )));
        $this->metadataFactory->addMetadata($metadata);

        // The constraints of both groups failed
        $violations = new Symfony_Component_Validator_ConstraintViolationList();
        $violations->add(new Symfony_Component_Validator_ConstraintViolation(
            'Failed',
            'Failed',
            array(),
            $entity,
            'firstName',
            ''
        ));
        $violations->add(new Symfony_Component_Validator_ConstraintViolation(
            'Failed',
            'Failed',
            array(),
            $entity,
            'lastName',
            ''
        ));

        $result = $this->validator->validate($entity, array('First', 'Second'));

        $this->assertEquals($violations, $result);
    }

    public function testValidateGroupSequenceProvider()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_GroupSequenceProviderEntity();
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata(get_class($entity));
        $metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint(array(
            'groups' => 'First',
        )));
        $metadata->addPropertyConstraint('lastName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint(array(
            'groups' => 'Second',
        )));
        $metadata->setGroupSequenceProvider(true);
        $this->metadataFactory->addMetadata($metadata);

        $violations = new Symfony_Component_Validator_ConstraintViolationList();
        $violations->add(new Symfony_Component_Validator_ConstraintViolation(
            'Failed',
            'Failed',
            array(),
            $entity,
            'firstName',
            ''
        ));

        $entity->setGroups(array('First'));
        $result = $this->validator->validate($entity);
        $this->assertEquals($violations, $result);

        $violations = new Symfony_Component_Validator_ConstraintViolationList();
        $violations->add(new Symfony_Component_Validator_ConstraintViolation(
            'Failed',
            'Failed',
            array(),
            $entity,
            'lastName',
            ''
        ));

        $entity->setGroups(array('Second'));
        $result = $this->validator->validate($entity);
        $this->assertEquals($violations, $result);

        $entity->setGroups(array());
        $result = $this->validator->validate($entity);
        $this->assertEquals(new Symfony_Component_Validator_ConstraintViolationList(), $result);
    }

    public function testValidateProperty()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata(get_class($entity));
        $metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());
        $this->metadataFactory->addMetadata($metadata);

        $result = $this->validator->validateProperty($entity, 'firstName');

        $this->assertCount(1, $result);

        $result = $this->validator->validateProperty($entity, 'lastName');

        $this->assertCount(0, $result);
    }

    public function testValidatePropertyValue()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata(get_class($entity));
        $metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint());
        $this->metadataFactory->addMetadata($metadata);

        $result = $this->validator->validatePropertyValue(get_class($entity), 'firstName', 'Bernhard');

        $this->assertCount(1, $result);
    }

    public function testValidateValue()
    {
        $violations = new Symfony_Component_Validator_ConstraintViolationList();
        $violations->add(new Symfony_Component_Validator_ConstraintViolation(
            'Failed',
            'Failed',
            array(),
            '',
            '',
            'Bernhard'
        ));

        $this->assertEquals($violations, $this->validator->validateValue('Bernhard', new Symfony_Component_Validator_Tests_Fixtures_FailingConstraint()));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_ValidatorException
     */
    public function testValidateValueRejectsValid()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity();
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata(get_class($entity));
        $this->metadataFactory->addMetadata($metadata);

        $this->validator->validateValue($entity, new Symfony_Component_Validator_Constraints_Valid());
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_ValidatorException
     */
    public function testValidatePropertyFailsIfPropertiesNotSupported()
    {
        // $metadata does not implement PropertyMetadataContainerInterface
        $metadata = $this->getMock('Symfony_Component_Validator_MetadataInterface');
        $this->metadataFactory = $this->getMock('Symfony_Component_Validator_MetadataFactoryInterface');
        $this->metadataFactory->expects($this->any())
            ->method('getMetadataFor')
            ->with('VALUE')
            ->will($this->returnValue($metadata));
        $this->validator = new Symfony_Component_Validator_Validator($this->metadataFactory, new Symfony_Component_Validator_ConstraintValidatorFactory(), new Symfony_Component_Validator_DefaultTranslator());

        $this->validator->validateProperty('VALUE', 'someProperty');
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_ValidatorException
     */
    public function testValidatePropertyValueFailsIfPropertiesNotSupported()
    {
        // $metadata does not implement PropertyMetadataContainerInterface
        $metadata = $this->getMock('Symfony_Component_Validator_MetadataInterface');
        $this->metadataFactory = $this->getMock('Symfony_Component_Validator_MetadataFactoryInterface');
        $this->metadataFactory->expects($this->any())
            ->method('getMetadataFor')
            ->with('VALUE')
            ->will($this->returnValue($metadata));
        $this->validator = new Symfony_Component_Validator_Validator($this->metadataFactory, new Symfony_Component_Validator_ConstraintValidatorFactory(), new Symfony_Component_Validator_DefaultTranslator());

        $this->validator->validatePropertyValue('VALUE', 'someProperty', 'propertyValue');
    }

    public function testGetMetadataFactory()
    {
        $this->assertInstanceOf(
            'Symfony_Component_Validator_MetadataFactoryInterface',
            $this->validator->getMetadataFactory()
        );
    }
}
