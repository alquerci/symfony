<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Fragment_Tests_FragmentRenderer_HIncludeFragmentRendererTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }
    }

    /**
     * @expectedException LogicException
     */
    public function testRenderExceptionWhenControllerAndNoSigner()
    {
        $strategy = new Symfony_Component_HttpKernel_Fragment_HIncludeFragmentRenderer();
        $strategy->render(new Symfony_Component_HttpKernel_Controller_ControllerReference('main_controller', array(), array()), Symfony_Component_HttpFoundation_Request::create('/'));
    }

    public function testRenderWithControllerAndSigner()
    {
        $strategy = new Symfony_Component_HttpKernel_Fragment_HIncludeFragmentRenderer(null, new Symfony_Component_HttpKernel_UriSigner('foo'));

        $this->assertEquals('<hx:include src="http://localhost/_fragment?_path=_format%3Dhtml%26_controller%3Dmain_controller&amp;_hash=VI25qJj8J0qveB3bGKPhsJtexKg%3D"></hx:include>', $strategy->render(new Symfony_Component_HttpKernel_Controller_ControllerReference('main_controller', array(), array()), Symfony_Component_HttpFoundation_Request::create('/'))->getContent());
    }

    public function testRenderWithUri()
    {
        $strategy = new Symfony_Component_HttpKernel_Fragment_HIncludeFragmentRenderer();
        $this->assertEquals('<hx:include src="/foo"></hx:include>', $strategy->render('/foo', Symfony_Component_HttpFoundation_Request::create('/'))->getContent());

        $strategy = new Symfony_Component_HttpKernel_Fragment_HIncludeFragmentRenderer(null, new Symfony_Component_HttpKernel_UriSigner('foo'));
        $this->assertEquals('<hx:include src="/foo"></hx:include>', $strategy->render('/foo', Symfony_Component_HttpFoundation_Request::create('/'))->getContent());
    }

    public function testRenderWhithDefault()
    {
        // only default
        $strategy = new Symfony_Component_HttpKernel_Fragment_HIncludeFragmentRenderer();
        $this->assertEquals('<hx:include src="/foo">default</hx:include>', $strategy->render('/foo', Symfony_Component_HttpFoundation_Request::create('/'), array('default' => 'default'))->getContent());

        // only global default
        $strategy = new Symfony_Component_HttpKernel_Fragment_HIncludeFragmentRenderer(null, null, 'global_default');
        $this->assertEquals('<hx:include src="/foo">global_default</hx:include>', $strategy->render('/foo', Symfony_Component_HttpFoundation_Request::create('/'), array())->getContent());

        // global default and default
        $strategy = new Symfony_Component_HttpKernel_Fragment_HIncludeFragmentRenderer(null, null, 'global_default');
        $this->assertEquals('<hx:include src="/foo">default</hx:include>', $strategy->render('/foo', Symfony_Component_HttpFoundation_Request::create('/'), array('default' => 'default'))->getContent());
    }
}
