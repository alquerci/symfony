<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Controller_ControllerNameParserTest extends Symfony_Bundle_FrameworkBundle_Tests_TestCase
{
    protected $loader;

    protected function setUp()
    {
        $this->loader = new Symfony_Component_ClassLoader_ClassLoader();
        $this->loader->addPrefixes(array(
            'TestBundle'      => dirname(__FILE__).'/../Fixtures',
            'TestApplication' => dirname(__FILE__).'/../Fixtures',
        ));
        $this->loader->register();
    }

    public function tearDown()
    {
        spl_autoload_unregister(array($this->loader, 'loadClass'));

        $this->loader = null;
    }

    public function testParse()
    {
        $parser = $this->createParser();

        $this->assertEquals('TestBundle_FooBundle_Controller_DefaultController::indexAction', $parser->parse('FooBundle:Default:index'), '->parse() converts a short a:b:c notation string to a class::method string');
        $this->assertEquals('TestBundle_FooBundle_Controller_Sub_DefaultController::indexAction', $parser->parse('FooBundle:Sub_Default:index'), '->parse() converts a short a:b:c notation string to a class::method string');
        $this->assertEquals('TestBundle_Fabpot_FooBundle_Controller_DefaultController::indexAction', $parser->parse('SensioFooBundle:Default:index'), '->parse() converts a short a:b:c notation string to a class::method string');
        $this->assertEquals('TestBundle_Sensio_Cms_FooBundle_Controller_DefaultController::indexAction', $parser->parse('SensioCmsFooBundle:Default:index'), '->parse() converts a short a:b:c notation string to a class::method string');
        $this->assertEquals('TestBundle_FooBundle_Controller_Test_DefaultController::indexAction', $parser->parse('FooBundle:Test_Default:index'), '->parse() converts a short a:b:c notation string to a class::method string');
        $this->assertEquals('TestBundle_FooBundle_Controller_Test_DefaultController::indexAction', $parser->parse('FooBundle:Test/Default:index'), '->parse() converts a short a:b:c notation string to a class::method string');

        try {
            $parser->parse('foo:');
            $this->fail('->parse() throws an InvalidArgumentException if the controller is not an a:b:c string');
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('InvalidArgumentException'), '->parse() throws an InvalidArgumentException if the controller is not an a:b:c string');
        }
    }

    /**
     * @dataProvider getMissingControllersTest
     */
    public function testMissingControllers($name)
    {
        $parser = $this->createParser();

        try {
            $parser->parse($name);
            $this->fail('->parse() throws a InvalidArgumentException if the string is in the valid format, but not matching class can be found');
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('InvalidArgumentException'), '->parse() throws a InvalidArgumentException if the class is found but does not exist');
        }
    }

    public function getMissingControllersTest()
    {
        return array(
            array('FooBundle:Fake:index'),          // a normal bundle
            array('SensioFooBundle:Fake:index'),    // a bundle with children
        );
    }

    private function createParser()
    {
        $this->closureBundles = array(
            'SensioFooBundle' => array($this->getBundle('TestBundle_Fabpot_FooBundle', 'FabpotFooBundle'), $this->getBundle('TestBundle_Sensio_FooBundle', 'SensioFooBundle')),
            'SensioCmsFooBundle' => array($this->getBundle('TestBundle_Sensio_Cms_FooBundle', 'SensioCmsFooBundle')),
            'FooBundle' => array($this->getBundle('TestBundle_FooBundle', 'FooBundle')),
            'FabpotFooBundle' => array($this->getBundle('TestBundle_Fabpot_FooBundle', 'FabpotFooBundle'), $this->getBundle('TestBundle_Sensio_FooBundle', 'SensioFooBundle')),
        );

        $kernel = $this->getMock('Symfony_Component_HttpKernel_KernelInterface');
        $kernel
            ->expects($this->any())
            ->method('getBundle')
            ->will($this->returnCallback(array($this, 'closureCreateParser')))
        ;

        return new Symfony_Bundle_FrameworkBundle_Controller_ControllerNameParser($kernel);
    }
    private $closureBundles = array();
    public function closureCreateParser($bundle)
    {
        return $this->closureBundles[$bundle];
    }

    private function getBundle($namespace, $name)
    {
        $bundle = $this->getMock('Symfony_Component_HttpKernel_Bundle_BundleInterface');
        $bundle->expects($this->any())->method('getName')->will($this->returnValue($name));
        $bundle->expects($this->any())->method('getNamespace')->will($this->returnValue($namespace));

        return $bundle;
    }
}
