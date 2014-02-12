<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Authorization_Voter_RoleVoterTest extends PHPUnit_Framework_TestCase
{
    public function testSupportsClass()
    {
        $voter = new Symfony_Component_Security_Core_Authorization_Voter_RoleVoter();

        $this->assertTrue($voter->supportsClass('Foo'));
    }

    /**
     * @dataProvider getVoteTests
     */
    public function testVote($roles, $attributes, $expected)
    {
        $voter = new Symfony_Component_Security_Core_Authorization_Voter_RoleVoter();

        $this->assertSame($expected, $voter->vote($this->getToken($roles), null, $attributes));
    }

    public function getVoteTests()
    {
        return array(
            array(array(), array(), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_ABSTAIN),
            array(array(), array('FOO'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_ABSTAIN),
            array(array(), array('ROLE_FOO'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_DENIED),
            array(array('ROLE_FOO'), array('ROLE_FOO'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_GRANTED),
            array(array('ROLE_FOO'), array('FOO', 'ROLE_FOO'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_GRANTED),
            array(array('ROLE_BAR', 'ROLE_FOO'), array('ROLE_FOO'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_GRANTED),
        );
    }

    protected function getToken(array $roles)
    {
        foreach ($roles as $i => $role) {
            $roles[$i] = new Symfony_Component_Security_Core_Role_Role($role);
        }
        $token = $this->getMock('Symfony_Component_Security_Core_Authentication_Token_TokenInterface');
        $token->expects($this->once())
              ->method('getRoles')
              ->will($this->returnValue($roles));
        ;

        return $token;
    }
}
