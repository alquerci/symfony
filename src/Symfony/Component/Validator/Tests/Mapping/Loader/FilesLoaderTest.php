<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Mapping_Loader_FilesLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testCallsGetFileLoaderInstanceForeachPath()
    {
        $loader = $this->getFilesLoader($this->getFileLoader());
        $this->assertEquals(4, $loader->getTimesCalled());
    }

    public function testCallsActualFileLoaderForMetadata()
    {
        $fileLoader = $this->getFileLoader();
        $fileLoader->expects($this->exactly(4))
            ->method('loadClassMetadata');
        $loader = $this->getFilesLoader($fileLoader);
        $loader->loadClassMetadata(new Symfony_Component_Validator_Mapping_ClassMetadata('Symfony_Component_Validator_Tests_Fixtures_Entity'));
    }

    public function getFilesLoader(Symfony_Component_Validator_Mapping_Loader_LoaderInterface $loader)
    {
        return $this->getMockForAbstractClass('Symfony_Component_Validator_Tests_Fixtures_FilesLoader', array(array(
            dirname(__FILE__) . '/constraint-mapping.xml',
            dirname(__FILE__) . '/constraint-mapping.yaml',
            dirname(__FILE__) . '/constraint-mapping.test',
            dirname(__FILE__) . '/constraint-mapping.txt',
        ), $loader));
    }

    public function getFileLoader()
    {
        return $this->getMock('Symfony_Component_Validator_Mapping_Loader_LoaderInterface');
    }
}
