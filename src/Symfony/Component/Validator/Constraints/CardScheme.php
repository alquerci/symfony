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
 * Metadata for the CardSchemeValidator.
 *
 * @Annotation
 */
class Symfony_Component_Validator_Constraints_CardScheme extends Symfony_Component_Validator_Constraint
{
    public $message = 'Unsupported card type or invalid card number.';
    public $schemes;

    public function getDefaultOption()
    {
        return 'schemes';
    }

    public function getRequiredOptions()
    {
        return array('schemes');
    }
}
