<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Routing_Tests_Generator_Dumper_PhpGeneratorDumperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony_Component_Routing_RouteCollection
     */
    private $routeCollection;

    /**
     * @var Symfony_Component_Routing_Generator_Dumper_PhpGeneratorDumper
     */
    private $generatorDumper;

    /**
     * @var string
     */
    private $testTmpFilepath;

    protected function setUp()
    {
        parent::setUp();

        $this->routeCollection = new Symfony_Component_Routing_RouteCollection();
        $this->generatorDumper = new Symfony_Component_Routing_Generator_Dumper_PhpGeneratorDumper($this->routeCollection);
        $this->testTmpFilepath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'php_generator.php';
        @unlink($this->testTmpFilepath);
    }

    protected function tearDown()
    {
        parent::tearDown();

        @unlink($this->testTmpFilepath);

        $this->routeCollection = null;
        $this->generatorDumper = null;
        $this->testTmpFilepath = null;
    }

    public function testDumpWithRoutes()
    {
        $this->routeCollection->add('Test', new Symfony_Component_Routing_Route('/testing/{foo}'));
        $this->routeCollection->add('Test2', new Symfony_Component_Routing_Route('/testing2'));

        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump());
        include ($this->testTmpFilepath);

        $projectUrlGenerator = new ProjectUrlGenerator(new Symfony_Component_Routing_RequestContext('/app.php'));

        $absoluteUrlWithParameter    = $projectUrlGenerator->generate('Test', array('foo' => 'bar'), true);
        $absoluteUrlWithoutParameter = $projectUrlGenerator->generate('Test2', array(), true);
        $relativeUrlWithParameter    = $projectUrlGenerator->generate('Test', array('foo' => 'bar'), false);
        $relativeUrlWithoutParameter = $projectUrlGenerator->generate('Test2', array(), false);

        $this->assertEquals($absoluteUrlWithParameter, 'http://localhost/app.php/testing/bar');
        $this->assertEquals($absoluteUrlWithoutParameter, 'http://localhost/app.php/testing2');
        $this->assertEquals($relativeUrlWithParameter, '/app.php/testing/bar');
        $this->assertEquals($relativeUrlWithoutParameter, '/app.php/testing2');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDumpWithoutRoutes()
    {
        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump(array('class' => 'WithoutRoutesUrlGenerator')));
        include ($this->testTmpFilepath);

        $projectUrlGenerator = new WithoutRoutesUrlGenerator(new Symfony_Component_Routing_RequestContext('/app.php'));

        $projectUrlGenerator->generate('Test', array());
    }

    /**
     * @expectedException Symfony_Component_Routing_Exception_RouteNotFoundException
     */
    public function testGenerateNonExistingRoute()
    {
        $this->routeCollection->add('Test', new Symfony_Component_Routing_Route('/test'));

        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump(array('class' => 'NonExistingRoutesUrlGenerator')));
        include ($this->testTmpFilepath);

        $projectUrlGenerator = new NonExistingRoutesUrlGenerator(new Symfony_Component_Routing_RequestContext());
        $url = $projectUrlGenerator->generate('NonExisting', array());
    }

    public function testDumpForRouteWithDefaults()
    {
        $this->routeCollection->add('Test', new Symfony_Component_Routing_Route('/testing/{foo}', array('foo' => 'bar')));

        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump(array('class' => 'DefaultRoutesUrlGenerator')));
        include ($this->testTmpFilepath);

        $projectUrlGenerator = new DefaultRoutesUrlGenerator(new Symfony_Component_Routing_RequestContext());
        $url = $projectUrlGenerator->generate('Test', array());

        $this->assertEquals($url, '/testing');
    }
}
