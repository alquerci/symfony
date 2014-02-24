<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Exception_MissingOptionsException extends Symfony_Component_Validator_Exception_ValidatorException
{
    private $options;

    public function __construct($message, array $options)
    {
        parent::__construct($message);

        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
