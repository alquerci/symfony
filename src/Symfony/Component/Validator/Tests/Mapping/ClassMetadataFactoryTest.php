<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Mapping_ClassMetadataFactoryTest extends PHPUnit_Framework_TestCase
{
    const CLASSNAME = 'Symfony_Component_Validator_Tests_Fixtures_Entity';
    const PARENTCLASS = 'Symfony_Component_Validator_Tests_Fixtures_EntityParent';

    public function handle($errorNumber, $message, $file, $line, $context)
    {
        if ($errorNumber & E_USER_DEPRECATED) {
            return true;
        }

        return PHPUnit_Util_ErrorHandler::handleError($errorNumber, $message, $file, $line);
    }

    public function testLoadClassMetadata()
    {
        $factory = new Symfony_Component_Validator_Mapping_ClassMetadataFactory(new Symfony_Component_Validator_Tests_Mapping_TestLoader());
        set_error_handler(array($this, 'handle'));
        $metadata = $factory->getClassMetadata(self::PARENTCLASS);
        restore_error_handler();

        $constraints = array(
            new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => array('Default', 'EntityParent'))),
        );

        $this->assertEquals($constraints, $metadata->getConstraints());
    }

    public function testMergeParentConstraints()
    {
        $factory = new Symfony_Component_Validator_Mapping_ClassMetadataFactory(new Symfony_Component_Validator_Tests_Mapping_TestLoader());
        set_error_handler(array($this, 'handle'));
        $metadata = $factory->getClassMetadata(self::CLASSNAME);
        restore_error_handler();

        $constraints = array(
            new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => array(
                'Default',
                'EntityParent',
                'Entity',
            ))),
            new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => array(
                'Default',
                'EntityInterface',
                'Entity',
            ))),
            new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => array(
                'Default',
                'Entity',
            ))),
        );

        $this->assertEquals($constraints, $metadata->getConstraints());
    }

    public function testWriteMetadataToCache()
    {
        $cache = $this->getMock('Symfony_Component_Validator_Mapping_Cache_CacheInterface');
        $factory = new Symfony_Component_Validator_Mapping_ClassMetadataFactory(new Symfony_Component_Validator_Tests_Mapping_TestLoader(), $cache);

        $tester = $this;
        $constraints = array(
            new Symfony_Component_Validator_Tests_Fixtures_ConstraintA(array('groups' => array('Default', 'EntityParent'))),
        );

        $cache->expects($this->never())
              ->method('has');
        $cache->expects($this->once())
              ->method('read')
              ->with($this->equalTo(self::PARENTCLASS))
              ->will($this->returnValue(false));
        $cache->expects($this->once())
              ->method('write')
              ->will($this->returnCallback(function($metadata) use ($tester, $constraints) {
                  $tester->assertEquals($constraints, $metadata->getConstraints());
              }));

        set_error_handler(array($this, 'handle'));
        $metadata = $factory->getClassMetadata(self::PARENTCLASS);
        restore_error_handler();

        $this->assertEquals(self::PARENTCLASS, $metadata->getClassName());
        $this->assertEquals($constraints, $metadata->getConstraints());
    }

    public function testReadMetadataFromCache()
    {
        $loader = $this->getMock('Symfony_Component_Validator_Mapping_Loader_LoaderInterface');
        $cache = $this->getMock('Symfony_Component_Validator_Mapping_Cache_CacheInterface');
        $factory = new Symfony_Component_Validator_Mapping_ClassMetadataFactory($loader, $cache);

        $tester = $this;
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata(self::PARENTCLASS);
        $metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());

        $loader->expects($this->never())
               ->method('loadClassMetadata');

        $cache->expects($this->never())
              ->method('has');
        $cache->expects($this->once())
              ->method('read')
              ->will($this->returnValue($metadata));

        set_error_handler(array($this, 'handle'));
        $this->assertEquals($metadata,$factory->getClassMetadata(self::PARENTCLASS));
        restore_error_handler();
    }
}

class Symfony_Component_Validator_Tests_Mapping_TestLoader implements Symfony_Component_Validator_Mapping_Loader_LoaderInterface
{
    public function loadClassMetadata(Symfony_Component_Validator_Mapping_ClassMetadata $metadata)
    {
        $metadata->addConstraint(new Symfony_Component_Validator_Tests_Fixtures_ConstraintA());
    }
}
