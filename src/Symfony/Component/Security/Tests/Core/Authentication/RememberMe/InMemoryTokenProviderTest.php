<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Authentication_RememberMe_InMemoryTokenProviderTest extends PHPUnit_Framework_TestCase
{
    public function testCreateNewToken()
    {
        $provider = new Symfony_Component_Security_Core_Authentication_RememberMe_InMemoryTokenProvider();

        $token = new Symfony_Component_Security_Core_Authentication_RememberMe_PersistentToken('foo', 'foo', 'foo', 'foo', new DateTime());
        $provider->createNewToken($token);

        $this->assertSame($provider->loadTokenBySeries('foo'), $token);
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_TokenNotFoundException
     */
    public function testLoadTokenBySeriesThrowsNotFoundException()
    {
        $provider = new Symfony_Component_Security_Core_Authentication_RememberMe_InMemoryTokenProvider();
        $provider->loadTokenBySeries('foo');
    }

    public function testUpdateToken()
    {
        $provider = new Symfony_Component_Security_Core_Authentication_RememberMe_InMemoryTokenProvider();

        $token = new Symfony_Component_Security_Core_Authentication_RememberMe_PersistentToken('foo', 'foo', 'foo', 'foo', new DateTime());
        $provider->createNewToken($token);
        $provider->updateToken('foo', 'newFoo', $lastUsed = new DateTime());
        $token = $provider->loadTokenBySeries('foo');

        $this->assertEquals('newFoo', $token->getTokenValue());
        $this->assertSame($token->getLastUsed(), $lastUsed);
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_TokenNotFoundException
     */
    public function testDeleteToken()
    {
        $provider = new Symfony_Component_Security_Core_Authentication_RememberMe_InMemoryTokenProvider();

        $token = new Symfony_Component_Security_Core_Authentication_RememberMe_PersistentToken('foo', 'foo', 'foo', 'foo', new DateTime());
        $provider->createNewToken($token);
        $provider->deleteTokenBySeries('foo');
        $provider->loadTokenBySeries('foo');
    }
}
