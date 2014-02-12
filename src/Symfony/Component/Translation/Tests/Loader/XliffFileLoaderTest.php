<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Translation_Tests_Loader_XliffFileLoaderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_Config_Loader_Loader')) {
            $this->markTestSkipped('The "Config" component is not available');
        }
    }

    public function testLoad()
    {
        $loader = new Symfony_Component_Translation_Loader_XliffFileLoader();
        $resource = dirname(__FILE__).'/../fixtures/resources.xlf';
        $catalogue = $loader->load($resource, 'en', 'domain1');

        $this->assertEquals('en', $catalogue->getLocale());
        $this->assertEquals(array(new Symfony_Component_Config_Resource_FileResource($resource)), $catalogue->getResources());
    }

    public function testLoadWithResname()
    {
        $loader = new Symfony_Component_Translation_Loader_XliffFileLoader();
        $catalogue = $loader->load(dirname(__FILE__).'/../fixtures/resname.xlf', 'en', 'domain1');

        $this->assertEquals(array('foo' => 'bar', 'bar' => 'baz', 'baz' => 'foo'), $catalogue->all('domain1'));
    }

    public function testIncompleteResource()
    {
        $loader = new Symfony_Component_Translation_Loader_XliffFileLoader();
        $catalogue = $loader->load(dirname(__FILE__).'/../fixtures/resources.xlf', 'en', 'domain1');

        $this->assertEquals(array('foo' => 'bar', 'key' => '', 'test' => 'with'), $catalogue->all('domain1'));
        $this->assertFalse($catalogue->has('extra', 'domain1'));
    }

    /**
     * @expectedException Symfony_Component_Translation_Exception_InvalidResourceException
     */
    public function testLoadInvalidResource()
    {
        $loader = new Symfony_Component_Translation_Loader_XliffFileLoader();
        $loader->load(dirname(__FILE__).'/../fixtures/resources.php', 'en', 'domain1');
    }

    /**
     * @expectedException Symfony_Component_Translation_Exception_InvalidResourceException
     */
    public function testLoadResourceDoesNotValidate()
    {
        $loader = new Symfony_Component_Translation_Loader_XliffFileLoader();
        $loader->load(dirname(__FILE__).'/../fixtures/non-valid.xlf', 'en', 'domain1');
    }

    /**
     * @expectedException Symfony_Component_Translation_Exception_NotFoundResourceException
     */
    public function testLoadNonExistingResource()
    {
        $loader = new Symfony_Component_Translation_Loader_XliffFileLoader();
        $resource = dirname(__FILE__).'/../fixtures/non-existing.xlf';
        $loader->load($resource, 'en', 'domain1');
    }

    /**
     * @expectedException Symfony_Component_Translation_Exception_InvalidResourceException
     */
    public function testLoadThrowsAnExceptionIfFileNotLocal()
    {
        $loader = new Symfony_Component_Translation_Loader_XliffFileLoader();
        $resource = 'http://example.com/resources.xlf';
        $loader->load($resource, 'en', 'domain1');
    }

    public function testDocTypeIsNotAllowed()
    {
        $loader = new Symfony_Component_Translation_Loader_XliffFileLoader();

        // document types are not allowed.
        try {
            $loader->load(dirname(__FILE__).'/../fixtures/withdoctype.xlf', 'en', 'domain1');
            $this->fail('->load() throws an InvalidArgumentException if the configuration contains a document type');
        } catch (Exception $e) {
            $this->assertInstanceOf('Symfony_Component_Translation_Exception_InvalidResourceException', $e, '->load() throws an InvalidArgumentException if the configuration contains a document type');
            $this->assertRegExp(sprintf('#^Unable to parse file ".+%s".$#', 'withdoctype.xlf'), $e->getMessage(), '->load() throws an InvalidArgumentException if the configuration contains a document type');

            $e = $e->getPrevious();
            $this->assertInstanceOf('InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if the configuration contains a document type');
            $this->assertSame('Document types are not allowed.', $e->getMessage(), '->load() throws an InvalidArgumentException if the configuration contains a document type');
        }
    }
}
