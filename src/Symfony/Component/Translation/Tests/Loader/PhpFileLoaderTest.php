<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Translation_Tests_Loader_PhpFileLoaderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_Config_Loader_Loader')) {
            $this->markTestSkipped('The "Config" component is not available');
        }
    }

    public function testLoad()
    {
        $loader = new Symfony_Component_Translation_Loader_PhpFileLoader();
        $resource = dirname(__FILE__).'/../fixtures/resources.php';
        $catalogue = $loader->load($resource, 'en', 'domain1');

        $this->assertEquals(array('foo' => 'bar'), $catalogue->all('domain1'));
        $this->assertEquals('en', $catalogue->getLocale());
        $this->assertEquals(array(new Symfony_Component_Config_Resource_FileResource($resource)), $catalogue->getResources());
    }

    /**
     * @expectedException Symfony_Component_Translation_Exception_NotFoundResourceException
     */
    public function testLoadNonExistingResource()
    {
        $loader = new Symfony_Component_Translation_Loader_PhpFileLoader();
        $resource = dirname(__FILE__).'/../fixtures/non-existing.php';
        $loader->load($resource, 'en', 'domain1');
    }

    /**
     * @expectedException Symfony_Component_Translation_Exception_InvalidResourceException
     */
    public function testLoadThrowsAnExceptionIfFileNotLocal()
    {
        $loader = new Symfony_Component_Translation_Loader_PhpFileLoader();
        $resource = 'http://example.com/resources.php';
        $loader->load($resource, 'en', 'domain1');
    }
}
