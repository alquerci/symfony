<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Http_Logout_SessionLogoutHandlerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }
    }

    public function testLogout()
    {
        $handler = new Symfony_Component_Security_Http_Logout_SessionLogoutHandler();

        $request = $this->getMock('Symfony_Component_HttpFoundation_Request');
        $response = new Symfony_Component_HttpFoundation_Response();
        $session = $this->getMock('Symfony_Component_HttpFoundation_Session_Session', array(), array(), '', false);

        $request
            ->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($session))
        ;

        $session
            ->expects($this->once())
            ->method('invalidate')
        ;

        $handler->logout($request, $response, $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface'));
    }
}
