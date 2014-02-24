<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Constraints_CollectionValidatorArrayObjectTest extends Symfony_Component_Validator_Tests_Constraints_CollectionValidatorTest
{
    public function prepareTestData(array $contents)
    {
        return new ArrayObject($contents);
    }
}
