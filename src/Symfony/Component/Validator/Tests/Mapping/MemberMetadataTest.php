<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Mapping_MemberMetadataTest extends PHPUnit_Framework_TestCase
{
    protected $metadata;

    protected function setUp()
    {
        $this->metadata = new Symfony_Component_Validator_Tests_Mapping_TestMemberMetadata(
            'Symfony_Component_Validator_Tests_Fixtures_Entity',
            'getLastName',
            'lastName'
        );
    }

    protected function tearDown()
    {
        $this->metadata = null;
    }

    public function testAddValidSetsMemberToCascaded()
    {
        $result = $this->metadata->addConstraint(new Symfony_Component_Validator_Constraints_Valid());

        $this->assertEquals(array(), $this->metadata->getConstraints());
        $this->assertEquals($result, $this->metadata);
        $this->assertTrue($this->metadata->isCascaded());
    }

    public function testAddOtherConstraintDoesNotSetMemberToCascaded()
    {
        $result = $this->metadata->addConstraint($constraint = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->assertEquals(array($constraint), $this->metadata->getConstraints());
        $this->assertEquals($result, $this->metadata);
        $this->assertFalse($this->metadata->isCascaded());
    }

    public function testAddConstraintRequiresClassConstraints()
    {
        $this->setExpectedException('Symfony_Component_Validator_Exception_ConstraintDefinitionException');

        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ClassConstraint());
    }

    public function testSerialize()
    {
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('property1' => 'A')));
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintB(array('groups' => 'TestGroup')));

        $metadata = unserialize(serialize($this->metadata));

        $this->assertEquals($this->metadata, $metadata);
    }
}

class Symfony_Component_Validator_Tests_Mapping_TestMemberMetadata extends Symfony_Component_Validator_Mapping_MemberMetadata
{
    public function getPropertyValue($object)
    {
    }

    protected function newReflectionMember()
    {
    }
}
