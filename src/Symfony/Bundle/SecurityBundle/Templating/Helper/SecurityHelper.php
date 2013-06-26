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
 * SecurityHelper provides read-only access to the security context.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_SecurityBundle_Templating_Helper_SecurityHelper extends Symfony_Component_Templating_Helper_Helper
{
    private $context;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Security_Core_SecurityContextInterface $context A SecurityContext instance
     */
    public function __construct(Symfony_Component_Security_Core_SecurityContextInterface $context = null)
    {
        $this->context = $context;
    }

    public function isGranted($role, $object = null, $field = null)
    {
        if (null === $this->context) {
            return false;
        }

        if (null !== $field) {
            $object = new Symfony_Component_Security_Acl_Voter_FieldVote($object, $field);
        }

        return $this->context->isGranted($role, $object);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'security';
    }
}
