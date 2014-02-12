<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Authentication_RememberMe_PersistentTokenTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $lastUsed = new DateTime();
        $token = new Symfony_Component_Security_Core_Authentication_RememberMe_PersistentToken('fooclass', 'fooname', 'fooseries', 'footokenvalue', $lastUsed);

        $this->assertEquals('fooclass', $token->getClass());
        $this->assertEquals('fooname', $token->getUsername());
        $this->assertEquals('fooseries', $token->getSeries());
        $this->assertEquals('footokenvalue', $token->getTokenValue());
        $this->assertSame($lastUsed, $token->getLastUsed());
    }
}
