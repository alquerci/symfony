<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Mapping_Loader_StaticMethodLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testLoadClassMetadataReturnsTrueIfSuccessful()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_StaticMethodLoader('loadMetadata');
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Mapping_Loader_StaticLoaderEntity');

        $this->assertTrue($loader->loadClassMetadata($metadata));
    }

    public function testLoadClassMetadataReturnsFalseIfNotSuccessful()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_StaticMethodLoader('loadMetadata');
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('stdClass');

        $this->assertFalse($loader->loadClassMetadata($metadata));
    }

    public function testLoadClassMetadata()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_StaticMethodLoader('loadMetadata');
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Mapping_Loader_StaticLoaderEntity');

        $loader->loadClassMetadata($metadata);

        $this->assertEquals(Symfony_Component_Validator_Tests_Mapping_Loader_StaticLoaderEntity::$invokedWith, $metadata);
    }

    public function testLoadClassMetadataDoesNotRepeatLoadWithParentClasses()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_StaticMethodLoader('loadMetadata');
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Mapping_Loader_StaticLoaderDocument');
        $loader->loadClassMetadata($metadata);
        $this->assertSame(0, count($metadata->getConstraints()));

        $loader = new Symfony_Component_Validator_Mapping_Loader_StaticMethodLoader('loadMetadata');
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Mapping_Loader_BaseStaticLoaderDocument');
        $loader->loadClassMetadata($metadata);
        $this->assertSame(1, count($metadata->getConstraints()));
    }

    public function testLoadClassMetadataIgnoresInterfaces()
    {
        $loader = new Symfony_Component_Validator_Mapping_Loader_StaticMethodLoader('loadMetadata');
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Mapping_Loader_StaticLoaderInterface');

        $loader->loadClassMetadata($metadata);

        $this->assertSame(0, count($metadata->getConstraints()));
    }
}

interface Symfony_Component_Validator_Tests_Mapping_Loader_StaticLoaderInterface
{
    public static function loadMetadata(Symfony_Component_Validator_Mapping_ClassMetadata $metadata);
}

class Symfony_Component_Validator_Tests_Mapping_Loader_StaticLoaderEntity
{
    public static $invokedWith = null;

    public static function loadMetadata(Symfony_Component_Validator_Mapping_ClassMetadata $metadata)
    {
        self::$invokedWith = $metadata;
    }
}

class Symfony_Component_Validator_Tests_Mapping_Loader_StaticLoaderDocument extends Symfony_Component_Validator_Tests_Mapping_Loader_BaseStaticLoaderDocument
{
}

class Symfony_Component_Validator_Tests_Mapping_Loader_BaseStaticLoaderDocument
{
    public static function loadMetadata(Symfony_Component_Validator_Mapping_ClassMetadata $metadata)
    {
        $metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());
    }
}
