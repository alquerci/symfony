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
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Validator_Tests_Constraints_CountValidatorArrayTest extends Symfony_Component_Validator_Tests_Constraints_CountValidatorTest
{
    protected function createCollection(array $content)
    {
        return $content;
    }
}
