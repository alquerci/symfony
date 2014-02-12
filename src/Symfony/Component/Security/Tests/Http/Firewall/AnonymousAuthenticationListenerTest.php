<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Http_Firewall_AnonymousAuthenticationListenerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }
    }

    public function testHandleWithContextHavingAToken()
    {
        $context = $this->getMock('Symfony_Component_Security_Core_SecurityContextInterface');
        $context
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface')))
        ;
        $context
            ->expects($this->never())
            ->method('setToken')
        ;

        $listener = new Symfony_Component_Security_Http_Firewall_AnonymousAuthenticationListener($context, 'TheKey');
        $listener->handle($this->getMock('Symfony_Component_HttpKernel_Event_GetResponseEvent', array(), array(), '', false));
    }

    public function testHandleWithContextHavingNoToken()
    {
        $context = $this->getMock('Symfony_Component_Security_Core_SecurityContextInterface');
        $context
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue(null))
        ;
        $context
            ->expects($this->once())
            ->method('setToken')
            ->with(self::logicalAnd(
                $this->isInstanceOf('Symfony_Component_Security_Core_Authentication_Token_AnonymousToken'),
                $this->attributeEqualTo('key', 'TheKey')
            ))
        ;

        $listener = new Symfony_Component_Security_Http_Firewall_AnonymousAuthenticationListener($context, 'TheKey');
        $listener->handle($this->getMock('Symfony_Component_HttpKernel_Event_GetResponseEvent', array(), array(), '', false));
    }
}
