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
 *
 * @api
 */
class Symfony_Component_Validator_Constraints_Locale extends Symfony_Component_Validator_Constraint
{
    public $message = 'This value is not a valid locale.';
}
