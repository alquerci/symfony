<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Authentication_Provider_DaoAuthenticationProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Symfony_Component_Security_Core_Exception_AuthenticationServiceException
     */
    public function testRetrieveUserWhenProviderDoesNotReturnAnUserInterface()
    {
        $provider = $this->getProvider('fabien');
        $method = new ReflectionMethod($provider, 'retrieveUser');

        $method->invoke($provider, 'fabien', $this->getSupportedToken());
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_UsernameNotFoundException
     */
    public function testRetrieveUserWhenUsernameIsNotFound()
    {
        $userProvider = $this->getMock('Symfony_Component_Security_Core_User_UserProviderInterface');
        $userProvider->expects($this->once())
                     ->method('loadUserByUsername')
                     ->will($this->throwException($this->getMock('Symfony_Component_Security_Core_Exception_UsernameNotFoundException', null, array(), '', false)))
        ;

        $provider = new Symfony_Component_Security_Tests_Core_Authentication_Provider_DaoAuthenticationProvider($userProvider, $this->getMock('Symfony_Component_Security_Core_User_UserCheckerInterface'), 'key', $this->getMock('Symfony_Component_Security_Core_Encoder_EncoderFactoryInterface'));
        $method = new ReflectionMethod($provider, 'retrieveUser');

        $method->invoke($provider, 'fabien', $this->getSupportedToken());
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_AuthenticationServiceException
     */
    public function testRetrieveUserWhenAnExceptionOccurs()
    {
        $userProvider = $this->getMock('Symfony_Component_Security_Core_User_UserProviderInterface');
        $userProvider->expects($this->once())
                     ->method('loadUserByUsername')
                     ->will($this->throwException($this->getMock('RuntimeException', null, array(), '', false)))
        ;

        $provider = new Symfony_Component_Security_Tests_Core_Authentication_Provider_DaoAuthenticationProvider($userProvider, $this->getMock('Symfony_Component_Security_Core_User_UserCheckerInterface'), 'key', $this->getMock('Symfony_Component_Security_Core_Encoder_EncoderFactoryInterface'));
        $method = new ReflectionMethod($provider, 'retrieveUser');

        $method->invoke($provider, 'fabien', $this->getSupportedToken());
    }

    public function testRetrieveUserReturnsUserFromTokenOnReauthentication()
    {
        $userProvider = $this->getMock('Symfony_Component_Security_Core_User_UserProviderInterface');
        $userProvider->expects($this->never())
                     ->method('loadUserByUsername')
        ;

        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $token = $this->getSupportedToken();
        $token->expects($this->once())
              ->method('getUser')
              ->will($this->returnValue($user))
        ;

        $provider = new Symfony_Component_Security_Tests_Core_Authentication_Provider_DaoAuthenticationProvider($userProvider, $this->getMock('Symfony_Component_Security_Core_User_UserCheckerInterface'), 'key', $this->getMock('Symfony_Component_Security_Core_Encoder_EncoderFactoryInterface'));
        $reflection = new ReflectionMethod($provider, 'retrieveUser');
        $result = $reflection->invoke($provider, null, $token);

        $this->assertSame($user, $result);
    }

    public function testRetrieveUser()
    {
        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');

        $userProvider = $this->getMock('Symfony_Component_Security_Core_User_UserProviderInterface');
        $userProvider->expects($this->once())
                     ->method('loadUserByUsername')
                     ->will($this->returnValue($user))
        ;

        $provider = new Symfony_Component_Security_Tests_Core_Authentication_Provider_DaoAuthenticationProvider($userProvider, $this->getMock('Symfony_Component_Security_Core_User_UserCheckerInterface'), 'key', $this->getMock('Symfony_Component_Security_Core_Encoder_EncoderFactoryInterface'));
        $method = new ReflectionMethod($provider, 'retrieveUser');

        $this->assertSame($user, $method->invoke($provider, 'fabien', $this->getSupportedToken()));
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_BadCredentialsException
     */
    public function testCheckAuthenticationWhenCredentialsAreEmpty()
    {
        $encoder = $this->getMock('Symfony_Component_Security_Core_Encoder_PasswordEncoderInterface');
        $encoder
            ->expects($this->never())
            ->method('isPasswordValid')
        ;

        $provider = $this->getProvider(false, false, $encoder);
        $method = new ReflectionMethod($provider, 'checkAuthentication');

        $token = $this->getSupportedToken();
        $token
            ->expects($this->once())
            ->method('getCredentials')
            ->will($this->returnValue(''))
        ;

        $method->invoke(
            $provider,
            $this->getMock('Symfony_Component_Security_Core_User_UserInterface'),
            $token
        );
    }

    public function testCheckAuthenticationWhenCredentialsAre0()
    {
        $encoder = $this->getMock('Symfony_Component_Security_Core_Encoder_PasswordEncoderInterface');
        $encoder
            ->expects($this->once())
            ->method('isPasswordValid')
            ->will($this->returnValue(true))
        ;

        $provider = $this->getProvider(false, false, $encoder);
        $method = new ReflectionMethod($provider, 'checkAuthentication');

        $token = $this->getSupportedToken();
        $token
            ->expects($this->once())
            ->method('getCredentials')
            ->will($this->returnValue('0'))
        ;

        $method->invoke(
            $provider,
            $this->getMock('Symfony_Component_Security_Core_User_UserInterface'),
            $token
        );
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_BadCredentialsException
     */
    public function testCheckAuthenticationWhenCredentialsAreNotValid()
    {
        $encoder = $this->getMock('Symfony_Component_Security_Core_Encoder_PasswordEncoderInterface');
        $encoder->expects($this->once())
                ->method('isPasswordValid')
                ->will($this->returnValue(false))
        ;

        $provider = $this->getProvider(false, false, $encoder);
        $method = new ReflectionMethod($provider, 'checkAuthentication');

        $token = $this->getSupportedToken();
        $token->expects($this->once())
              ->method('getCredentials')
              ->will($this->returnValue('foo'))
        ;

        $method->invoke($provider, $this->getMock('Symfony_Component_Security_Core_User_UserInterface'), $token);
    }

    /**
     * @expectedException Symfony_Component_Security_Core_Exception_BadCredentialsException
     */
    public function testCheckAuthenticationDoesNotReauthenticateWhenPasswordHasChanged()
    {
        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $user->expects($this->once())
             ->method('getPassword')
             ->will($this->returnValue('foo'))
        ;

        $token = $this->getSupportedToken();
        $token->expects($this->once())
              ->method('getUser')
              ->will($this->returnValue($user));

        $dbUser = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $dbUser->expects($this->once())
               ->method('getPassword')
               ->will($this->returnValue('newFoo'))
        ;

        $provider = $this->getProvider(false, false, null);
        $reflection = new ReflectionMethod($provider, 'checkAuthentication');
        $reflection->invoke($provider, $dbUser, $token);
    }

    public function testCheckAuthenticationWhenTokenNeedsReauthenticationWorksWithoutOriginalCredentials()
    {
        $user = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $user->expects($this->once())
             ->method('getPassword')
             ->will($this->returnValue('foo'))
        ;

        $token = $this->getSupportedToken();
        $token->expects($this->once())
              ->method('getUser')
              ->will($this->returnValue($user));

        $dbUser = $this->getMock('Symfony_Component_Security_Core_User_UserInterface');
        $dbUser->expects($this->once())
               ->method('getPassword')
               ->will($this->returnValue('foo'))
        ;

        $provider = $this->getProvider(false, false, null);
        $reflection = new ReflectionMethod($provider, 'checkAuthentication');
        $reflection->invoke($provider, $dbUser, $token);
    }

    public function testCheckAuthentication()
    {
        $encoder = $this->getMock('Symfony_Component_Security_Core_Encoder_PasswordEncoderInterface');
        $encoder->expects($this->once())
                ->method('isPasswordValid')
                ->will($this->returnValue(true))
        ;

        $provider = $this->getProvider(false, false, $encoder);
        $method = new ReflectionMethod($provider, 'checkAuthentication');

        $token = $this->getSupportedToken();
        $token->expects($this->once())
              ->method('getCredentials')
              ->will($this->returnValue('foo'))
        ;

        $method->invoke($provider, $this->getMock('Symfony_Component_Security_Core_User_UserInterface'), $token);
    }

    protected function getSupportedToken()
    {
        $mock = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken', array('getCredentials', 'getUser', 'getProviderKey'), array(), '', false);
        $mock
            ->expects($this->any())
            ->method('getProviderKey')
            ->will($this->returnValue('key'))
        ;

        return $mock;
    }

    protected function getProvider($user = false, $userChecker = false, $passwordEncoder = null)
    {
        $userProvider = $this->getMock('Symfony_Component_Security_Core_User_UserProviderInterface');
        if (false !== $user) {
            $userProvider->expects($this->once())
                         ->method('loadUserByUsername')
                         ->will($this->returnValue($user))
            ;
        }

        if (false === $userChecker) {
            $userChecker = $this->getMock('Symfony_Component_Security_Core_User_UserCheckerInterface');
        }

        if (null === $passwordEncoder) {
            $passwordEncoder = new Symfony_Component_Security_Core_Encoder_PlaintextPasswordEncoder();
        }

        $encoderFactory = $this->getMock('Symfony_Component_Security_Core_Encoder_EncoderFactoryInterface');
        $encoderFactory
            ->expects($this->any())
            ->method('getEncoder')
            ->will($this->returnValue($passwordEncoder))
        ;

        return new Symfony_Component_Security_Tests_Core_Authentication_Provider_DaoAuthenticationProvider($userProvider, $userChecker, 'key', $encoderFactory);
    }
}

class Symfony_Component_Security_Tests_Core_Authentication_Provider_DaoAuthenticationProvider extends Symfony_Component_Security_Core_Authentication_Provider_DaoAuthenticationProvider
{
    public function checkAuthentication(Symfony_Component_Security_Core_User_UserInterface $user, Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken $token)
    {
        return parent::checkAuthentication($user, $token);
    }

    public function retrieveUser($username, Symfony_Component_Security_Core_Authentication_Token_UsernamePasswordToken $token)
    {
        return parent::retrieveUser($username, $token);
    }
}
