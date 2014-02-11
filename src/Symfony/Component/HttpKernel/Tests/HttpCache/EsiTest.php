<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_HttpCache_EsiTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }
    }

    public function testHasSurrogateEsiCapability()
    {
        $esi = new Symfony_Component_HttpKernel_HttpCache_Esi();

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $request->headers->set('Surrogate-Capability', 'abc="ESI/1.0"');
        $this->assertTrue($esi->hasSurrogateEsiCapability($request));

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $request->headers->set('Surrogate-Capability', 'foobar');
        $this->assertFalse($esi->hasSurrogateEsiCapability($request));

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $this->assertFalse($esi->hasSurrogateEsiCapability($request));
    }

    public function testAddSurrogateEsiCapability()
    {
        $esi = new Symfony_Component_HttpKernel_HttpCache_Esi();

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $esi->addSurrogateEsiCapability($request);
        $this->assertEquals('symfony2="ESI/1.0"', $request->headers->get('Surrogate-Capability'));

        $esi->addSurrogateEsiCapability($request);
        $this->assertEquals('symfony2="ESI/1.0", symfony2="ESI/1.0"', $request->headers->get('Surrogate-Capability'));
    }

    public function testAddSurrogateControl()
    {
        $esi = new Symfony_Component_HttpKernel_HttpCache_Esi();

        $response = new Symfony_Component_HttpFoundation_Response('foo <esi:include src="" />');
        $esi->addSurrogateControl($response);
        $this->assertEquals('content="ESI/1.0"', $response->headers->get('Surrogate-Control'));

        $response = new Symfony_Component_HttpFoundation_Response('foo');
        $esi->addSurrogateControl($response);
        $this->assertEquals('', $response->headers->get('Surrogate-Control'));
    }

    public function testNeedsEsiParsing()
    {
        $esi = new Symfony_Component_HttpKernel_HttpCache_Esi();

        $response = new Symfony_Component_HttpFoundation_Response();
        $response->headers->set('Surrogate-Control', 'content="ESI/1.0"');
        $this->assertTrue($esi->needsEsiParsing($response));

        $response = new Symfony_Component_HttpFoundation_Response();
        $this->assertFalse($esi->needsEsiParsing($response));
    }

    public function testRenderIncludeTag()
    {
        $esi = new Symfony_Component_HttpKernel_HttpCache_Esi();

        $this->assertEquals('<esi:include src="/" onerror="continue" alt="/alt" />', $esi->renderIncludeTag('/', '/alt', true));
        $this->assertEquals('<esi:include src="/" alt="/alt" />', $esi->renderIncludeTag('/', '/alt', false));
        $this->assertEquals('<esi:include src="/" onerror="continue" />', $esi->renderIncludeTag('/'));
        $this->assertEquals('<esi:comment text="some comment" />'."\n".'<esi:include src="/" onerror="continue" alt="/alt" />', $esi->renderIncludeTag('/', '/alt', true, 'some comment'));
    }

    public function testProcessDoesNothingIfContentTypeIsNotHtml()
    {
        $esi = new Symfony_Component_HttpKernel_HttpCache_Esi();

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $response = new Symfony_Component_HttpFoundation_Response();
        $response->headers->set('Content-Type', 'text/plain');
        $esi->process($request, $response);

        $this->assertFalse($response->headers->has('x-body-eval'));
    }

    public function testProcess()
    {
        $esi = new Symfony_Component_HttpKernel_HttpCache_Esi();

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $response = new Symfony_Component_HttpFoundation_Response('foo <esi:comment text="some comment" /><esi:include src="..." alt="alt" onerror="continue" />');
        $esi->process($request, $response);

        $this->assertEquals('foo <?php echo $this->esi->handle($this, \'...\', \'alt\', true) ?>'."\n", $response->getContent());
        $this->assertEquals('ESI', $response->headers->get('x-body-eval'));

        $response = new Symfony_Component_HttpFoundation_Response('foo <esi:include src="..." />');
        $esi->process($request, $response);

        $this->assertEquals('foo <?php echo $this->esi->handle($this, \'...\', \'\', false) ?>'."\n", $response->getContent());

        $response = new Symfony_Component_HttpFoundation_Response('foo <esi:include src="..."></esi:include>');
        $esi->process($request, $response);

        $this->assertEquals('foo <?php echo $this->esi->handle($this, \'...\', \'\', false) ?>'."\n", $response->getContent());
    }

    public function testProcessEscapesPhpTags()
    {
        $esi = new Symfony_Component_HttpKernel_HttpCache_Esi();

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $response = new Symfony_Component_HttpFoundation_Response('foo <?php die("foo"); ?><%= "lala" %>');
        $esi->process($request, $response);

        $this->assertEquals('foo <?php echo "<?"; ?>php die("foo"); ?><?php echo "<%"; ?>= "lala" %>', $response->getContent());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testProcessWhenNoSrcInAnEsi()
    {
        $esi = new Symfony_Component_HttpKernel_HttpCache_Esi();

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $response = new Symfony_Component_HttpFoundation_Response('foo <esi:include />');
        $esi->process($request, $response);
    }

    public function testProcessRemoveSurrogateControlHeader()
    {
        $esi = new Symfony_Component_HttpKernel_HttpCache_Esi();

        $request = Symfony_Component_HttpFoundation_Request::create('/');
        $response = new Symfony_Component_HttpFoundation_Response('foo <esi:include src="..." />');
        $response->headers->set('Surrogate-Control', 'content="ESI/1.0"');
        $esi->process($request, $response);
        $this->assertEquals('ESI', $response->headers->get('x-body-eval'));

        $response->headers->set('Surrogate-Control', 'no-store, content="ESI/1.0"');
        $esi->process($request, $response);
        $this->assertEquals('ESI', $response->headers->get('x-body-eval'));
        $this->assertEquals('no-store', $response->headers->get('surrogate-control'));

        $response->headers->set('Surrogate-Control', 'content="ESI/1.0", no-store');
        $esi->process($request, $response);
        $this->assertEquals('ESI', $response->headers->get('x-body-eval'));
        $this->assertEquals('no-store', $response->headers->get('surrogate-control'));
    }

    public function testHandle()
    {
        $esi = new Symfony_Component_HttpKernel_HttpCache_Esi();
        $cache = $this->getCache(Symfony_Component_HttpFoundation_Request::create('/'), new Symfony_Component_HttpFoundation_Response('foo'));
        $this->assertEquals('foo', $esi->handle($cache, '/', '/alt', true));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testHandleWhenResponseIsNot200()
    {
        $esi = new Symfony_Component_HttpKernel_HttpCache_Esi();
        $response = new Symfony_Component_HttpFoundation_Response('foo');
        $response->setStatusCode(404);
        $cache = $this->getCache(Symfony_Component_HttpFoundation_Request::create('/'), $response);
        $esi->handle($cache, '/', '/alt', false);
    }

    public function testHandleWhenResponseIsNot200AndErrorsAreIgnored()
    {
        $esi = new Symfony_Component_HttpKernel_HttpCache_Esi();
        $response = new Symfony_Component_HttpFoundation_Response('foo');
        $response->setStatusCode(404);
        $cache = $this->getCache(Symfony_Component_HttpFoundation_Request::create('/'), $response);
        $this->assertEquals('', $esi->handle($cache, '/', '/alt', true));
    }

    public function testHandleWhenResponseIsNot200AndAltIsPresent()
    {
        $esi = new Symfony_Component_HttpKernel_HttpCache_Esi();
        $response1 = new Symfony_Component_HttpFoundation_Response('foo');
        $response1->setStatusCode(404);
        $response2 = new Symfony_Component_HttpFoundation_Response('bar');
        $cache = $this->getCache(Symfony_Component_HttpFoundation_Request::create('/'), array($response1, $response2));
        $this->assertEquals('bar', $esi->handle($cache, '/', '/alt', false));
    }

    protected function getCache($request, $response)
    {
        $cache = $this->getMock('Symfony_Component_HttpKernel_HttpCache_HttpCache', array('getRequest', 'handle'), array(), '', false);
        $cache->expects($this->any())
              ->method('getRequest')
              ->will($this->returnValue($request))
        ;
        if (is_array($response)) {
            $cache->expects($this->any())
                  ->method('handle')
                  ->will(call_user_func_array(array($this, 'onConsecutiveCalls'), $response))
            ;
        } else {
            $cache->expects($this->any())
                  ->method('handle')
                  ->will($this->returnValue($response))
            ;
        }

        return $cache;
    }
}
