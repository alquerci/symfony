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
 * Annotation for group sequences
 *
 * @Annotation
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
class Symfony_Component_Validator_Constraints_GroupSequence
{
    /**
     * The members of the sequence
     * @var array
     */
    public $groups;

    public function __construct(array $groups)
    {
        $this->groups = $groups['value'];
    }
}
