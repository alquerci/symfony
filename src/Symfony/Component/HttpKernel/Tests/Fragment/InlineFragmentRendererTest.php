<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Fragment_Tests_FragmentRenderer_InlineFragmentRendererTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }
    }

    public function testRender()
    {
        $strategy = new Symfony_Component_HttpKernel_Fragment_InlineFragmentRenderer($this->getKernel($this->returnValue(new Symfony_Component_HttpFoundation_Response('foo'))));

        $this->assertEquals('foo', $strategy->render('/', Symfony_Component_HttpFoundation_Request::create('/'))->getContent());
    }

    public function testRenderWithControllerReference()
    {
        $strategy = new Symfony_Component_HttpKernel_Fragment_InlineFragmentRenderer($this->getKernel($this->returnValue(new Symfony_Component_HttpFoundation_Response('foo'))));

        $this->assertEquals('foo', $strategy->render(new Symfony_Component_HttpKernel_Controller_ControllerReference('main_controller', array(), array()), Symfony_Component_HttpFoundation_Request::create('/'))->getContent());
    }

    public function testRenderWithObjectsAsAttributes()
    {
        $object = new stdClass();

        $subRequest = Symfony_Component_HttpFoundation_Request::create('/_fragment?_path=_format%3Dhtml%26_controller%3Dmain_controller');
        $subRequest->attributes->replace(array(
            'object'      => $object,
            '_format'     => 'html',
            '_controller' => 'main_controller',
        ));

        $kernel = $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface');
        $kernel
            ->expects($this->any())
            ->method('handle')
            ->with($subRequest)
        ;

        $strategy = new Symfony_Component_HttpKernel_Fragment_InlineFragmentRenderer($kernel);

        $strategy->render(new Symfony_Component_HttpKernel_Controller_ControllerReference('main_controller', array('object' => $object), array()), Symfony_Component_HttpFoundation_Request::create('/'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testRenderExceptionNoIgnoreErrors()
    {
        $strategy = new Symfony_Component_HttpKernel_Fragment_InlineFragmentRenderer($this->getKernel($this->throwException(new RuntimeException('foo'))));

        $this->assertEquals('foo', $strategy->render('/', Symfony_Component_HttpFoundation_Request::create('/'))->getContent());
    }

    public function testRenderExceptionIgnoreErrors()
    {
        $strategy = new Symfony_Component_HttpKernel_Fragment_InlineFragmentRenderer($this->getKernel($this->throwException(new RuntimeException('foo'))));

        $this->assertEmpty($strategy->render('/', Symfony_Component_HttpFoundation_Request::create('/'), array('ignore_errors' => true))->getContent());
    }

    public function testRenderExceptionIgnoreErrorsWithAlt()
    {
        $strategy = new Symfony_Component_HttpKernel_Fragment_InlineFragmentRenderer($this->getKernel($this->onConsecutiveCalls(
            $this->throwException(new RuntimeException('foo')),
            $this->returnValue(new Symfony_Component_HttpFoundation_Response('bar'))
        )));

        $this->assertEquals('bar', $strategy->render('/', Symfony_Component_HttpFoundation_Request::create('/'), array('ignore_errors' => true, 'alt' => '/foo'))->getContent());
    }

    private function getKernel($returnValue)
    {
        $kernel = $this->getMock('Symfony_Component_HttpKernel_HttpKernelInterface');
        $kernel
            ->expects($this->any())
            ->method('handle')
            ->will($returnValue)
        ;

        return $kernel;
    }

    public function testExceptionInSubRequestsDoesNotMangleOutputBuffers()
    {
        $resolver = $this->getMock('Symfony_Component_HttpKernel_Controller_ControllerResolverInterface');
        $resolver
            ->expects($this->once())
            ->method('getController')
            ->will($this->returnValue(create_function('', '
                ob_start();
                echo "bar";
                throw new RuntimeException();
            ')))
        ;
        $resolver
            ->expects($this->once())
            ->method('getArguments')
            ->will($this->returnValue(array()))
        ;

        $kernel = new Symfony_Component_HttpKernel_HttpKernel(new Symfony_Component_EventDispatcher_EventDispatcher(), $resolver);
        $renderer = new Symfony_Component_HttpKernel_Fragment_InlineFragmentRenderer($kernel);

        // simulate a main request with output buffering
        ob_start();
        echo 'Foo';

        // simulate a sub-request with output buffering and an exception
        $renderer->render('/', Symfony_Component_HttpFoundation_Request::create('/'), array('ignore_errors' => true));

        $this->assertEquals('Foo', ob_get_clean());
    }
}
