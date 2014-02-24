<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Constraints_FileValidatorObjectTest extends Symfony_Component_Validator_Tests_Constraints_FileValidatorTest
{
    protected function getFile($filename)
    {
        return new Symfony_Component_HttpFoundation_File_File($filename);
    }
}
