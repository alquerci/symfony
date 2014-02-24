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
 * @Annotation
 */
class Symfony_Component_Validator_Constraints_Collection_Optional extends Symfony_Component_Validator_Constraint
{
    public $constraints = array();

    public function getDefaultOption()
    {
        return 'constraints';
    }
}
