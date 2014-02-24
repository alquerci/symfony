<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Mapping_PropertyMetadataTest extends PHPUnit_Framework_TestCase
{
    const CLASSNAME = 'Symfony_Component_Validator_Tests_Fixtures_Entity';

    public function testInvalidPropertyName()
    {
        $this->setExpectedException('Symfony_Component_Validator_Exception_ValidatorException');

        new Symfony_Component_Validator_Mapping_PropertyMetadata(self::CLASSNAME, 'foobar');
    }

    public function testGetPropertyValueFromPrivateProperty()
    {
        $entity = new Symfony_Component_Validator_Tests_Fixtures_Entity('foobar');
        $metadata = new Symfony_Component_Validator_Mapping_PropertyMetadata(self::CLASSNAME, 'internal');

        $this->assertEquals('foobar', $metadata->getPropertyValue($entity));
    }
}
