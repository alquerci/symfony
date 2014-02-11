<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_Debug_ExceptionHandlerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }
    }

    public function testDebug()
    {
        $handler = new Symfony_Component_HttpKernel_Debug_ExceptionHandler(false);
        $response = $handler->createResponse(new RuntimeException('Foo'));

        $this->assertContains('<h1>Whoops, looks like something went wrong.</h1>', $response->getContent());
        $this->assertNotContains('<div class="block_exception clear_fix">', $response->getContent());

        $handler = new Symfony_Component_HttpKernel_Debug_ExceptionHandler(true);
        $response = $handler->createResponse(new RuntimeException('Foo'));

        $this->assertContains('<h1>Whoops, looks like something went wrong.</h1>', $response->getContent());
        $this->assertContains('<div class="block_exception clear_fix">', $response->getContent());
    }

    public function testStatusCode()
    {
        $handler = new Symfony_Component_HttpKernel_Debug_ExceptionHandler(false);

        $response = $handler->createResponse(new RuntimeException('Foo'));
        $this->assertEquals('500', $response->getStatusCode());
        $this->assertContains('Whoops, looks like something went wrong.', $response->getContent());

        $response = $handler->createResponse(new Symfony_Component_HttpKernel_Exception_NotFoundHttpException('Foo'));
        $this->assertEquals('404', $response->getStatusCode());
        $this->assertContains('Sorry, the page you are looking for could not be found.', $response->getContent());
    }

    public function testHeaders()
    {
        $handler = new Symfony_Component_HttpKernel_Debug_ExceptionHandler(false);

        $response = $handler->createResponse(new Symfony_Component_HttpKernel_Exception_MethodNotAllowedHttpException(array('POST')));
        $this->assertEquals('405', $response->getStatusCode());
        $this->assertEquals('POST', $response->headers->get('Allow'));
    }

    public function testNestedExceptions()
    {
        $handler = new Symfony_Component_HttpKernel_Debug_ExceptionHandler(true);
        $response = $handler->createResponse(new Symfony_Component_HttpKernel_Tests_Debug_RuntimeException('Foo', null, new RuntimeException('Bar')));
    }
}

class Symfony_Component_HttpKernel_Tests_Debug_RuntimeException extends RuntimeException
{
    /**
     * @var Exception
     */
    private $previous;

    /**
     * @param string     $message   The Exception message to throw. [Optional]
     * @param integer    $code      The error code                  [Optional]
     * @param Exception  $previous  A previous exception            [Optional]
     */
    public function __construct($message = '', $code = null, Exception $previous = null)
    {
        if (method_exists($this, 'getPrevious')) {
            parent::__construct($message, $code, $previous);
        } else {
            $this->previous = $previous;
            parent::__construct($message, $code);
        }
    }

    public function __call($name, array $arguments)
    {
        switch ($name) {
            case 'getPrevious':
                return $this->previous;
            default:
        }

        throw new BadMethodCallException(sprintf('Call an undefined method %s->%s(%s)',
            get_class($this),
            $name,
            implode(', ', array_map('gettype', $arguments))
        ));
    }
}
