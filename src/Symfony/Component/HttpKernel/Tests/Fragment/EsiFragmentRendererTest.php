<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_Fragment_FragmentRenderer_EsiFragmentRendererTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }
    }

    public function testRenderFallbackToInlineStrategyIfNoRequest()
    {
        $strategy = new Symfony_Component_HttpKernel_Fragment_EsiFragmentRenderer(new Symfony_Component_HttpKernel_HttpCache_Esi(), $this->getInlineStrategy(true));
        $strategy->render('/', Symfony_Component_HttpFoundation_Request::create('/'));
    }

    public function testRenderFallbackToInlineStrategyIfEsiNotSupported()
    {
        $strategy = new Symfony_Component_HttpKernel_Fragment_EsiFragmentRenderer(new Symfony_Component_HttpKernel_HttpCache_Esi(), $this->getInlineStrategy(true));
        $strategy->render('/', Symfony_Component_HttpFoundation_Request::create('/'));
    }

    public function testRender()
    {
        $strategy = new Symfony_Component_HttpKernel_Fragment_EsiFragmentRenderer(new Symfony_Component_HttpKernel_HttpCache_Esi(), $this->getInlineStrategy());

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $request->headers->set('Surrogate-Capability', 'ESI/1.0');

        $this->assertEquals('<esi:include src="/" />', $strategy->render('/', $request)->getContent());
        $this->assertEquals("<esi:comment text=\"This is a comment\" />\n<esi:include src=\"/\" />", $strategy->render('/', $request, array('comment' => 'This is a comment'))->getContent());
        $this->assertEquals('<esi:include src="/" alt="foo" />', $strategy->render('/', $request, array('alt' => 'foo'))->getContent());
        $this->assertEquals('<esi:include src="http://localhost/_fragment?_path=_format%3Dhtml%26_controller%3Dmain_controller" alt="http://localhost/_fragment?_path=_format%3Dhtml%26_controller%3Dalt_controller" />', $strategy->render(new Symfony_Component_HttpKernel_Controller_ControllerReference('main_controller', array(), array()), $request, array('alt' => new Symfony_Component_HttpKernel_Controller_ControllerReference('alt_controller', array(), array())))->getContent());
    }

    private function getInlineStrategy($called = false)
    {
        $inline = $this->getMockBuilder('Symfony_Component_HttpKernel_Fragment_InlineFragmentRenderer')->disableOriginalConstructor()->getMock();

        if ($called) {
            $inline->expects($this->once())->method('render');
        }

        return $inline;
    }
}
