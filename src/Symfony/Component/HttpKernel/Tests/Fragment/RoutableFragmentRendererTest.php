<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Fragment_Tests_FragmentRenderer_RoutableFragmentRendererTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getGenerateFragmentUriData
     */
    public function testGenerateFragmentUri($uri, $controller)
    {
        $this->assertEquals($uri, $this->getRenderer()->doGenerateFragmentUri($controller, Symfony_Component_HttpFoundation_Request::create('/')));
    }

    public function getGenerateFragmentUriData()
    {
        return array(
            array('http://localhost/_fragment?_path=_format%3Dhtml%26_controller%3Dcontroller', new Symfony_Component_HttpKernel_Controller_ControllerReference('controller', array(), array())),
            array('http://localhost/_fragment?_path=_format%3Dxml%26_controller%3Dcontroller', new Symfony_Component_HttpKernel_Controller_ControllerReference('controller', array('_format' => 'xml'), array())),
            array('http://localhost/_fragment?_path=foo%3Dfoo%26_format%3Djson%26_controller%3Dcontroller', new Symfony_Component_HttpKernel_Controller_ControllerReference('controller', array('foo' => 'foo', '_format' => 'json'), array())),
            array('http://localhost/_fragment?bar=bar&_path=foo%3Dfoo%26_format%3Dhtml%26_controller%3Dcontroller', new Symfony_Component_HttpKernel_Controller_ControllerReference('controller', array('foo' => 'foo'), array('bar' => 'bar'))),
            array('http://localhost/_fragment?foo=foo&_path=_format%3Dhtml%26_controller%3Dcontroller', new Symfony_Component_HttpKernel_Controller_ControllerReference('controller', array(), array('foo' => 'foo'))),
        );
    }

    public function testGenerateFragmentUriWithARequest()
    {
        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $request->attributes->set('_format', 'json');
        $controller = new Symfony_Component_HttpKernel_Controller_ControllerReference('controller', array(), array());

        $this->assertEquals('http://localhost/_fragment?_path=_format%3Djson%26_controller%3Dcontroller', $this->getRenderer()->doGenerateFragmentUri($controller, $request));
    }

    private function getRenderer()
    {
        return new Symfony_Component_HttpKernel_Fragment_Tests_FragmentRenderer_Renderer();
    }
}

class Symfony_Component_HttpKernel_Fragment_Tests_FragmentRenderer_Renderer extends Symfony_Component_HttpKernel_Fragment_RoutableFragmentRenderer
{
    public function render($uri, Symfony_Component_HttpFoundation_Request $request, array $options = array()) {}
    public function getName() {}

    public function doGenerateFragmentUri(Symfony_Component_HttpKernel_Controller_ControllerReference $reference, Symfony_Component_HttpFoundation_Request $request)
    {
        return parent::generateFragmentUri($reference, $request);
    }
}
