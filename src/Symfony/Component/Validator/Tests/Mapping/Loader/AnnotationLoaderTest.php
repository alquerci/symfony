<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Mapping_Loader_AnnotationLoaderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Doctrine_Common_Annotations_AnnotationReader')) {
            $this->markTestSkipped('The "Doctrine Common" library is not available');
        }
    }

    public function testLoadClassMetadataReturnsTrueIfSuccessful()
    {
        $reader = new Doctrine_Common_Annotations_AnnotationReader();
        $loader = new Symfony_Component_Validator_Mapping_Loader_AnnotationLoader($reader);
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_Entity');

        $this->assertTrue($loader->loadClassMetadata($metadata));
    }

    public function testLoadClassMetadataReturnsFalseIfNotSuccessful()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_AnnotationLoader(new Doctrine_Common_Annotations_AnnotationReader());
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('stdClass');

        $this->assertFalse($loader->loadClassMetadata($metadata));
    }

    public function testLoadClassMetadata()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_AnnotationLoader(new Doctrine_Common_Annotations_AnnotationReader());
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_Entity');

        $loader->loadClassMetadata($metadata);

        $expected = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_Entity');
        $expected->setGroupSequence(array('Foo', 'Entity'));
        $expected->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_NotNull());
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_Range(array('min' => 3)));
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_All(array(new Symfony_Component_Validator_Constraints_NotNull(), new Symfony_Component_Validator_Constraints_Range(array('min' => 3)))));
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_All(array('constraints' => array(new Symfony_Component_Validator_Constraints_NotNull(), new Symfony_Component_Validator_Constraints_Range(array('min' => 3))))));
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_Collection(array('fields' => array(
            'foo' => array(new Symfony_Component_Validator_Constraints_NotNull(), new Symfony_Component_Validator_Constraints_Range(array('min' => 3))),
            'bar' => new Symfony_Component_Validator_Constraints_Range(array('min' => 5)),
        ))));
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_Choice(array(
            'message' => 'Must be one of %choices%',
            'choices' => array('A', 'B'),
        )));
        $expected->addGetterConstraint('lastName', new Symfony_Component_Validator_Constraints_NotNull());

        // load reflection class so that the comparison passes
        $expected->getReflectionClass();

        $this->assertEquals($expected, $metadata);
    }

    /**
     * Test MetaData merge with parent annotation.
     */
    public function testLoadParentClassMetadata()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_AnnotationLoader(new Doctrine_Common_Annotations_AnnotationReader());

        // Load Parent MetaData
        $parent_metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_EntityParent');
        $loader->loadClassMetadata($parent_metadata);

        $expected_parent = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_EntityParent');
        $expected_parent->addPropertyConstraint('other', new Symfony_Component_Validator_Constraints_NotNull());
        $expected_parent->getReflectionClass();

        $this->assertEquals($expected_parent, $parent_metadata);
    }
    /**
     * Test MetaData merge with parent annotation.
     */
    public function testLoadClassMetadataAndMerge()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_AnnotationLoader(new Doctrine_Common_Annotations_AnnotationReader());

        // Load Parent MetaData
        $parent_metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_EntityParent');
        $loader->loadClassMetadata($parent_metadata);

        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_Entity');

        // Merge parent metaData.
        $metadata->mergeConstraints($parent_metadata);

        $loader->loadClassMetadata($metadata);

        $expected_parent = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_EntityParent');
        $expected_parent->addPropertyConstraint('other', new Symfony_Component_Validator_Constraints_NotNull());
        $expected_parent->getReflectionClass();

        $expected = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_Entity');
        $expected->mergeConstraints($expected_parent);

        $expected->setGroupSequence(array('Foo', 'Entity'));
        $expected->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_NotNull());
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_Range(array('min' => 3)));
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_All(array(new Symfony_Component_Validator_Constraints_NotNull(), new Symfony_Component_Validator_Constraints_Range(array('min' => 3)))));
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_All(array('constraints' => array(new Symfony_Component_Validator_Constraints_NotNull(), new Symfony_Component_Validator_Constraints_Range(array('min' => 3))))));
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_Collection(array('fields' => array(
            'foo' => array(new Symfony_Component_Validator_Constraints_NotNull(), new Symfony_Component_Validator_Constraints_Range(array('min' => 3))),
            'bar' => new Symfony_Component_Validator_Constraints_Range(array('min' => 5)),
        ))));
        $expected->addPropertyConstraint('firstName', new Symfony_Component_Validator_Constraints_Choice(array(
            'message' => 'Must be one of %choices%',
            'choices' => array('A', 'B'),
        )));
        $expected->addGetterConstraint('lastName', new Symfony_Component_Validator_Constraints_NotNull());

        // load reflection class so that the comparison passes
        $expected->getReflectionClass();

        $this->assertEquals($expected, $metadata);
    }

    public function testLoadGroupSequenceProviderAnnotation()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_AnnotationLoader(new Doctrine_Common_Annotations_AnnotationReader());

        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_GroupSequenceProviderEntity');
        $loader->loadClassMetadata($metadata);

        $expected = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_GroupSequenceProviderEntity');
        $expected->setGroupSequenceProvider(true);
        $expected->getReflectionClass();

        $this->assertEquals($expected, $metadata);
    }
}
