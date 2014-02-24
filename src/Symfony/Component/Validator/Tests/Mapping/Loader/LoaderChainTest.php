<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Mapping_Loader_LoaderChainTest extends PHPUnit_Framework_TestCase
{
    public function testAllLoadersAreCalled()
    {
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('stdClass');

        $loader1 = $this->getMock('Symfony_Component_Validator_Mapping_Loader_LoaderInterface');
        $loader1->expects($this->once())
                        ->method('loadClassMetadata')
                        ->with($this->equalTo($metadata));

        $loader2 = $this->getMock('Symfony_Component_Validator_Mapping_Loader_LoaderInterface');
        $loader2->expects($this->once())
                        ->method('loadClassMetadata')
                        ->with($this->equalTo($metadata));

        $chain = new Symfony_Component_Validator_Mapping_Loader_LoaderChain(array(
            $loader1,
            $loader2,
        ));

        $chain->loadClassMetadata($metadata);
    }

    public function testReturnsTrueIfAnyLoaderReturnedTrue()
    {
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('stdClass');

        $loader1 = $this->getMock('Symfony_Component_Validator_Mapping_Loader_LoaderInterface');
        $loader1->expects($this->any())
                        ->method('loadClassMetadata')
                        ->will($this->returnValue(true));

        $loader2 = $this->getMock('Symfony_Component_Validator_Mapping_Loader_LoaderInterface');
        $loader2->expects($this->any())
                        ->method('loadClassMetadata')
                        ->will($this->returnValue(false));

        $chain = new Symfony_Component_Validator_Mapping_Loader_LoaderChain(array(
            $loader1,
            $loader2,
        ));

        $this->assertTrue($chain->loadClassMetadata($metadata));
    }

    public function testReturnsFalseIfNoLoaderReturnedTrue()
    {
        $metadata = new Symfony_Component_Validator_Mapping_ClassMetadata('stdClass');

        $loader1 = $this->getMock('Symfony_Component_Validator_Mapping_Loader_LoaderInterface');
        $loader1->expects($this->any())
                        ->method('loadClassMetadata')
                        ->will($this->returnValue(false));

        $loader2 = $this->getMock('Symfony_Component_Validator_Mapping_Loader_LoaderInterface');
        $loader2->expects($this->any())
                        ->method('loadClassMetadata')
                        ->will($this->returnValue(false));

        $chain = new Symfony_Component_Validator_Mapping_Loader_LoaderChain(array(
            $loader1,
            $loader2,
        ));

        $this->assertFalse($chain->loadClassMetadata($metadata));
    }
}
