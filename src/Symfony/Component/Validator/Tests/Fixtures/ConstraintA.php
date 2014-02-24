<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/** @Annotation */
class Symfony_Component_Validator_Tests_Fixtures_ConstraintA extends Symfony_Component_Validator_Constraint
{
    public $property1;
    public $property2;

    public function getDefaultOption()
    {
        return 'property2';
    }

    public function getTargets()
    {
        return array(self::PROPERTY_CONSTRAINT, self::CLASS_CONSTRAINT);
    }
}
