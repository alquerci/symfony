<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Constraints_CountValidatorCountableTest_Countable implements Countable
{
    private $content;

    public function __construct(array $content)
    {
        $this->content = $content;
    }

    public function count()
    {
        return count($this->content);
    }
}

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Validator_Tests_Constraints_CountValidatorCountableTest extends Symfony_Component_Validator_Tests_Constraints_CountValidatorTest
{
    protected function createCollection(array $content)
    {
        return new Symfony_Component_Validator_Tests_Constraints_CountValidatorCountableTest_Countable($content);
    }
}
