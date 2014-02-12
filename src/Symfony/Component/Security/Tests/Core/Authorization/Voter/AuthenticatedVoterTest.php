<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Authorization_Voter_AuthenticatedVoterTest extends PHPUnit_Framework_TestCase
{
    public function testSupportsClass()
    {
        $voter = new Symfony_Component_Security_Core_Authorization_Voter_AuthenticatedVoter($this->getResolver());
        $this->assertTrue($voter->supportsClass('stdClass'));
    }

    /**
     * @dataProvider getVoteTests
     */
    public function testVote($authenticated, $attributes, $expected)
    {
        $voter = new Symfony_Component_Security_Core_Authorization_Voter_AuthenticatedVoter($this->getResolver());

        $this->assertSame($expected, $voter->vote($this->getToken($authenticated), null, $attributes));
    }

    public function getVoteTests()
    {
        return array(
            array('fully', array(), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_ABSTAIN),
            array('fully', array('FOO'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_ABSTAIN),
            array('remembered', array(), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_ABSTAIN),
            array('remembered', array('FOO'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_ABSTAIN),
            array('anonymously', array(), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_ABSTAIN),
            array('anonymously', array('FOO'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_ABSTAIN),

            array('fully', array('IS_AUTHENTICATED_ANONYMOUSLY'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_GRANTED),
            array('remembered', array('IS_AUTHENTICATED_ANONYMOUSLY'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_GRANTED),
            array('anonymously', array('IS_AUTHENTICATED_ANONYMOUSLY'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_GRANTED),

            array('fully', array('IS_AUTHENTICATED_REMEMBERED'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_GRANTED),
            array('remembered', array('IS_AUTHENTICATED_REMEMBERED'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_GRANTED),
            array('anonymously', array('IS_AUTHENTICATED_REMEMBERED'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_DENIED),

            array('fully', array('IS_AUTHENTICATED_FULLY'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_GRANTED),
            array('remembered', array('IS_AUTHENTICATED_FULLY'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_DENIED),
            array('anonymously', array('IS_AUTHENTICATED_FULLY'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_DENIED),
        );
    }

    protected function getResolver()
    {
        return new Symfony_Component_Security_Core_Authentication_AuthenticationTrustResolver(
            'Symfony_Component_Security_Core_Authentication_Token_AnonymousToken',
            'Symfony_Component_Security_Core_Authentication_Token_RememberMeToken'
        );
    }

    protected function getToken($authenticated)
    {
        if ('fully' === $authenticated) {
            return $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');
        } elseif ('remembered' === $authenticated) {
            return $this->getMock('Symfony_Component_Security_Core_Authentication_Token_RememberMeToken', array('setPersistent'), array(), '', false);
        } else {
            return $this->getMock('Symfony_Component_Security_Core_Authentication_Token_AnonymousToken', null, array('', ''));
        }
    }
}
