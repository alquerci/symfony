<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Constraints_FileValidatorPathTest extends Symfony_Component_Validator_Tests_Constraints_FileValidatorTest
{
    protected function getFile($filename)
    {
        return $filename;
    }

    public function testFileNotFound()
    {
        $constraint = new Symfony_Component_Validator_Constraints_File(array(
            'notFoundMessage' => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ file }}' => 'foobar',
            ));

        $this->validator->validate('foobar', $constraint);
    }
}
