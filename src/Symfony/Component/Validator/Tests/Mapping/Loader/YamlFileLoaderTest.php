<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Mapping_Loader_YamlFileLoaderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_Yaml_Yaml')) {
            $this->markTestSkipped('The "Yaml" component is not available');
        }
    }

    public function testLoadClassMetadataReturnsFalseIfEmpty()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_YamlFileLoader(dirname(__FILE__).'/empty-mapping.yml');
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_Entity');

        $this->assertFalse($loader->loadClassMetadata($metadata));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLoadClassMetadataThrowsExceptionIfNotAnArray()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_YamlFileLoader(dirname(__FILE__).'/nonvalid-mapping.yml');
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_Entity');
        $loader->loadClassMetadata($metadata);
    }

    public function testLoadClassMetadataReturnsTrueIfSuccessful()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_YamlFileLoader(dirname(__FILE__).'/constraint-mapping.yml');
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_Entity');

        $this->assertTrue($loader->loadClassMetadata($metadata));
    }

    public function testLoadClassMetadataReturnsFalseIfNotSuccessful()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_YamlFileLoader(dirname(__FILE__).'/constraint-mapping.yml');
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('stdClass');

        $this->assertFalse($loader->loadClassMetadata($metadata));
    }

    public function testLoadClassMetadata()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_YamlFileLoader(dirname(__FILE__).'/constraint-mapping.yml');
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_Entity');

        $loader->loadClassMetadata($metadata);

        $expected = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_Entity');
        $expected->setGroupSequence(array('Foo', 'Entity'));
        $expected->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());
        $expected->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintB());
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_NotNull());
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_Range(array('min' => 3)));
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_Choice(array('A', 'B')));
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_All(array(new Symfony_Component_Validator_Constraints_NotNull(), new Symfony_Component_Validator_Constraints_Range(array('min' => 3)))));
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_All(array('constraints' => array(new Symfony_Component_Validator_Constraints_NotNull(), new Symfony_Component_Validator_Constraints_Range(array('min' => 3))))));
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_Collection(array('fields' => array(
            'foo' => array(new Symfony_Component_Validator_Constraints_NotNull(), new Symfony_Component_Validator_Constraints_Range(array('min' => 3))),
            'bar' => array(new Symfony_Component_Validator_Constraints_Range(array('min' => 5))),
        ))));
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_Choice(array(
            'message' => 'Must be one of %choices%',
            'choices' => array('A', 'B'),
        )));
        $expected->addGetterConstraint('lastName', new Symfony_Component_Validator_Constraints_NotNull());

        $this->assertEquals($expected, $metadata);
    }

    public function testLoadGroupSequenceProvider()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_YamlFileLoader(dirname(__FILE__).'/constraint-mapping.yml');
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_GroupSequenceProviderEntity');

        $loader->loadClassMetadata($metadata);

        $expected = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_GroupSequenceProviderEntity');
        $expected->setGroupSequenceProvider(true);

        $this->assertEquals($expected, $metadata);
    }
}
