<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Mapping_ElementMetadataTest extends PHPUnit_Framework_TestCase
{
    protected $metadata;

    protected function setUp()
    {
        $this->metadata = new Symfony_Component_Validator_Tests_Mapping_TestElementMetadata('Symfony_Component_Validator_Tests_Fixtures_Entity');
    }

    protected function tearDown()
    {
        $this->metadata = null;
    }

    public function testAddConstraints()
    {
        $this->metadata->addConstraint($constraint1 = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());
        $this->metadata->addConstraint($constraint2 = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $this->assertEquals(array($constraint1, $constraint2), $this->metadata->getConstraints());
    }

    public function testMultipleConstraintsOfTheSameType()
    {
        $constraint1 = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('property1' => 'A'));
        $constraint2 = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('property1' => 'B'));

        $this->metadata->addConstraint($constraint1);
        $this->metadata->addConstraint($constraint2);

        $this->assertEquals(array($constraint1, $constraint2), $this->metadata->getConstraints());
    }

    public function testFindConstraintsByGroup()
    {
        $constraint1 = new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => 'TestGroup'));
        $constraint2 = new Symfony_Component_Validator_Tests_Fixtures_ConstraintB();

        $this->metadata->addConstraint($constraint1);
        $this->metadata->addConstraint($constraint2);

        $this->assertEquals(array($constraint1), $this->metadata->findConstraints('TestGroup'));
    }

    public function testSerialize()
    {
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('property1' => 'A')));
        $this->metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintB(array('groups' => 'TestGroup')));

        $metadata = unserialize(serialize($this->metadata));

        $this->assertEquals($this->metadata, $metadata);
    }
}

class Symfony_Component_Validator_Tests_Mapping_TestElementMetadata extends Symfony_Component_Validator_Mapping_ElementMetadata {}
