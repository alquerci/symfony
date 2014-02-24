<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @Symfony_Component_Validator_Constraints_GroupSequenceProvider
 */
class Symfony_Component_Validator_Tests_Fixtures_GroupSequenceProviderEntity implements Symfony_Component_Validator_GroupSequenceProviderInterface
{
    public $firstName;
    public $lastName;

    protected $groups = array();

    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    public function getGroupSequence()
    {
        return $this->groups;
    }
}
