<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Mapping_ClassMetadataTest extends PHPUnit_Framework_TestCase
{
    const CLASSNAME = 'Symfony_Component_Validator_Tests_Fixtures_Entity';
    const PARENTCLASS = 'Symfony_Component_Validator_Tests_Fixtures_EntityParent';
    const PROVIDERCLASS = 'Symfony_Component_Validator_Tests_Fixtures_GroupSequenceProviderEntity';

    protected $metadata;

    protected function setUp()
    {
        $this->metadata = new Symfony_Component_Validator_Mapping_ClassMetadata(self::CLASSNAME);
    }

    protected function tearDown()
    {
        $this->metadata = null;
    }

    public function testAddConstraintDoesNotAcceptValid()
    {
        $this->setExpectedException('Symfony_Component_Validator_Exception_ConstraintDefinitionException');

        $this->metadata->addConstraint(new Symfony_Component_Validator_Constraints_Valid());
    }

    public function testAddConstraintRequiresClassConstraints()
    {
        $this->setExpectedException('Symfony_Component_Validator_Exception_ConstraintDefinitionException');

        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_PropertyConstraint());
    }

    public function testAddPropertyConstraints()
    {
        $this->metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());
        $this->metadata->addPropertyConstraint('lastName', new Symfony_Component_Validator_Tests_Fixtures_ConstraintB());

        $this->assertEquals(array('firstName', 'lastName'), $this->metadata->getConstrainedProperties());
    }

    public function testMergeConstraintsMergesClassConstraints()
    {
        $parent = new Symfony_Component_Validator_Mapping_ClassMetadata(self::PARENTCLASS);
        $parent->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->metadata->mergeConstraints($parent);
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $constraints = array(
            new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => array(
                'Default',
                'EntityParent',
                'Entity',
            ))),
            new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => array(
                'Default',
                'Entity',
            ))),
        );

        $this->assertEquals($constraints, $this->metadata->getConstraints());
    }

    public function testMergeConstraintsMergesMemberConstraints()
    {
        $parent = new Symfony_Component_Validator_Mapping_ClassMetadata(self::PARENTCLASS);
        $parent->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->metadata->mergeConstraints($parent);
        $this->metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $constraints = array(
            new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => array(
                'Default',
                'EntityParent',
                'Entity',
            ))),
            new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => array(
                'Default',
                'Entity',
            ))),
        );

        $members = $this->metadata->getMemberMetadatas('firstName');

        $this->assertCount(1, $members);
        $this->assertEquals(self::PARENTCLASS, $members[0]->getClassName());
        $this->assertEquals($constraints, $members[0]->getConstraints());
    }

    public function testMemberMetadatas()
    {
        $this->metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->assertTrue($this->metadata->hasMemberMetadatas('firstName'));
        $this->assertFalse($this->metadata->hasMemberMetadatas('non_existant_field'));
    }

    public function testMergeConstraintsKeepsPrivateMembersSeparate()
    {
        $parent = new Symfony_Component_Validator_Mapping_ClassMetadata(self::PARENTCLASS);
        $parent->addPropertyConstraint('internal', new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->metadata->mergeConstraints($parent);
        $this->metadata->addPropertyConstraint('internal', new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $parentConstraints = array(
            new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => array(
                'Default',
                'EntityParent',
                'Entity',
            ))),
        );
        $constraints = array(
            new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => array(
                'Default',
                'Entity',
            ))),
        );

        $members = $this->metadata->getMemberMetadatas('internal');

        $this->assertCount(2, $members);
        $this->assertEquals(self::PARENTCLASS, $members[0]->getClassName());
        $this->assertEquals($parentConstraints, $members[0]->getConstraints());
        $this->assertEquals(self::CLASSNAME, $members[1]->getClassName());
        $this->assertEquals($constraints, $members[1]->getConstraints());
    }

    public function testGetReflectionClass()
    {
        $reflClass = new ReflectionClass(self::CLASSNAME);

        $this->assertEquals($reflClass, $this->metadata->getReflectionClass());
    }

    public function testSerialize()
    {
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('property1' => 'A')));
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintB(array('groups' => 'TestGroup')));
        $this->metadata->addPropertyConstraint('firstName', new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());
        $this->metadata->addGetterConstraint('lastName', new Symfony_Component_Validator_Tests_Fixtures_ConstraintB());

        $metadata = unserialize(serialize($this->metadata));

        $this->assertEquals($this->metadata, $metadata);
    }

    public function testGroupSequencesWorkIfContainingDefaultGroup()
    {
        $this->metadata->setGroupSequence(array('Foo', $this->metadata->getDefaultGroup()));
    }

    public function testGroupSequencesFailIfNotContainingDefaultGroup()
    {
        $this->setExpectedException('Symfony_Component_Validator_Exception_GroupDefinitionException');

        $this->metadata->setGroupSequence(array('Foo', 'Bar'));
    }

    public function testGroupSequencesFailIfContainingDefault()
    {
        $this->setExpectedException('Symfony_Component_Validator_Exception_GroupDefinitionException');

        $this->metadata->setGroupSequence(array('Foo', $this->metadata->getDefaultGroup(), Symfony_Component_Validator_Constraint::DEFAULT_GROUP));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_GroupDefinitionException
     */
    public function testGroupSequenceFailsIfGroupSequenceProviderIsSet()
    {
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata(self::PROVIDERCLASS);
        $metadata->setGroupSequenceProvider(true);
        $metadata->setGroupSequence(array('GroupSequenceProviderEntity', 'Foo'));
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_GroupDefinitionException
     */
    public function testGroupSequenceProviderFailsIfGroupSequenceIsSet()
    {
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata(self::PROVIDERCLASS);
        $metadata->setGroupSequence(array('GroupSequenceProviderEntity', 'Foo'));
        $metadata->setGroupSequenceProvider(true);
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_GroupDefinitionException
     */
    public function testGroupSequenceProviderFailsIfDomainClassIsInvalid()
    {
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('stdClass');
        $metadata->setGroupSequenceProvider(true);
    }

    public function testGroupSequenceProvider()
    {
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata(self::PROVIDERCLASS);
        $metadata->setGroupSequenceProvider(true);
        $this->assertTrue($metadata->isGroupSequenceProvider());
    }
}
