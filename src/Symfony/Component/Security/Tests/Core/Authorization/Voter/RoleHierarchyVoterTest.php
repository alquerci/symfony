<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Tests_Core_Authorization_Voter_RoleHierarchyVoterTest extends Symfony_Component_Security_Tests_Core_Authorization_Voter_RoleVoterTest
{
    /**
     * @dataProvider getVoteTests
     */
    public function testVote($roles, $attributes, $expected)
    {
        $voter = new Symfony_Component_Security_Core_Authorization_Voter_RoleHierarchyVoter(new Symfony_Component_Security_Core_Role_RoleHierarchy(array('ROLE_FOO' => array('ROLE_FOOBAR'))));

        $this->assertSame($expected, $voter->vote($this->getToken($roles), null, $attributes));
    }

    public function getVoteTests()
    {
        return array_merge(parent::getVoteTests(), array(
            array(array('ROLE_FOO'), array('ROLE_FOOBAR'), Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_GRANTED),
        ));
    }
}
