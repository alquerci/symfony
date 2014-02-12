<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Encoder_PasswordEncoder extends Symfony_Component_Security_Core_Encoder_BasePasswordEncoder
{
    public function encodePassword($raw, $salt)
    {
    }

    public function isPasswordValid($encoded, $raw, $salt)
    {
    }

    public function demergePasswordAndSalt($mergedPasswordSalt)
    {
        return parent::demergePasswordAndSalt($mergedPasswordSalt);
    }

    public function mergePasswordAndSalt($password, $salt)
    {
        return parent::mergePasswordAndSalt($password, $salt);
    }

    public function comparePasswords($password1, $password2)
    {
        return parent::comparePasswords($password1, $password2);
    }
}

class Symfony_Component_Security_Tests_Core_Encoder_BasePasswordEncoderTest extends PHPUnit_Framework_TestCase
{
    public function testComparePassword()
    {
        $this->assertTrue($this->invokeComparePasswords('password', 'password'));
        $this->assertFalse($this->invokeComparePasswords('password', 'foo'));
    }

    public function testDemergePasswordAndSalt()
    {
        $this->assertEquals(array('password', 'salt'), $this->invokeDemergePasswordAndSalt('password{salt}'));
        $this->assertEquals(array('password', ''), $this->invokeDemergePasswordAndSalt('password'));
        $this->assertEquals(array('', ''), $this->invokeDemergePasswordAndSalt(''));
    }

    public function testMergePasswordAndSalt()
    {
        $this->assertEquals('password{salt}', $this->invokeMergePasswordAndSalt('password', 'salt'));
        $this->assertEquals('password', $this->invokeMergePasswordAndSalt('password', ''));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMergePasswordAndSaltWithException()
    {
        $this->invokeMergePasswordAndSalt('password', '{foo}');
    }

    protected function invokeDemergePasswordAndSalt($password)
    {
        $encoder = new Symfony_Component_Security_Tests_Core_Encoder_PasswordEncoder();
        $r = new ReflectionObject($encoder);
        $m = $r->getMethod('demergePasswordAndSalt');

        return $m->invoke($encoder, $password);
    }

    protected function invokeMergePasswordAndSalt($password, $salt)
    {
        $encoder = new Symfony_Component_Security_Tests_Core_Encoder_PasswordEncoder();
        $r = new ReflectionObject($encoder);
        $m = $r->getMethod('mergePasswordAndSalt');

        return $m->invoke($encoder, $password, $salt);
    }

    protected function invokeComparePasswords($p1, $p2)
    {
        $encoder = new Symfony_Component_Security_Tests_Core_Encoder_PasswordEncoder();
        $r = new ReflectionObject($encoder);
        $m = $r->getMethod('comparePasswords');

        return $m->invoke($encoder, $p1, $p2);
    }
}
