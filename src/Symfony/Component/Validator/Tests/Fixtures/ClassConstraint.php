<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Fixtures_ClassConstraint extends Symfony_Component_Validator_Constraint
{
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
