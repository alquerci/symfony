<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Symfony_Component_Form_Tests_Extension_Validator_Type_TypeTestCase extends Symfony_Component_Form_Tests_Extension_Core_Type_TypeTestCase
{
    protected $validator;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_Validator_Constraint')) {
            $this->markTestSkipped('The "Validator" component is not available');
        }

        $this->validator = $this->getMock('Symfony_Component_Validator_ValidatorInterface');
        $metadataFactory = $this->getMock('Symfony_Component_Validator_MetadataFactoryInterface');
        $this->validator->expects($this->once())->method('getMetadataFactory')->will($this->returnValue($metadataFactory));
        $metadata = $this->getMockBuilder('Symfony_Component_Validator_Mapping_ClassMetadata')->disableOriginalConstructor()->getMock();
        $metadataFactory->expects($this->once())->method('getMetadataFor')->will($this->returnValue($metadata));

        parent::setUp();
    }

    protected function tearDown()
    {
        $this->validator = null;

        parent::tearDown();
    }

    protected function getExtensions()
    {
        return array_merge(parent::getExtensions(), array(
            new Symfony_Component_Form_Extension_Validator_ValidatorExtension($this->validator),
        ));
    }
}
