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
 * RoleVoter votes if any attribute starts with a given prefix.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Core_Authorization_Voter_RoleVoter implements Symfony_Component_Security_Core_Authorization_Voter_VoterInterface
{
    private $prefix;

    /**
     * Constructor.
     *
     * @param string $prefix The role prefix
     */
    public function __construct($prefix = 'ROLE_')
    {
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return 0 === strpos($attribute, $this->prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token, $object, array $attributes)
    {
        $result = Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_ABSTAIN;
        $roles = $this->extractRoles($token);

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $result = Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_DENIED;
            foreach ($roles as $role) {
                if ($attribute === $role->getRole()) {
                    return Symfony_Component_Security_Core_Authorization_Voter_VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return $result;
    }

    protected function extractRoles(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        return $token->getRoles();
    }
}
