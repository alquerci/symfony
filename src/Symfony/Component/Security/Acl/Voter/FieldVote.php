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
 * This class is a lightweight wrapper around field vote requests which does
 * not violate any interface contracts.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Security_Acl_Voter_FieldVote
{
    private $domainObject;
    private $field;

    public function __construct($domainObject, $field)
    {
        $this->domainObject = $domainObject;
        $this->field = $field;
    }

    public function getDomainObject()
    {
        return $this->domainObject;
    }

    public function getField()
    {
        return $this->field;
    }
}
