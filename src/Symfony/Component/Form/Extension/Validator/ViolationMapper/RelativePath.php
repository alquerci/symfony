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
class Symfony_Component_Form_Extension_Validator_ViolationMapper_RelativePath extends Symfony_Component_PropertyAccess_PropertyPath
{
    /**
     * @var Symfony_Component_Form_FormInterface
     */
    private $root;

    /**
     * @param Symfony_Component_Form_FormInterface $root
     * @param string        $propertyPath
     */
    public function __construct(Symfony_Component_Form_FormInterface $root, $propertyPath)
    {
        parent::__construct($propertyPath);

        $this->root = $root;
    }

    /**
     * @return Symfony_Component_Form_FormInterface
     */
    public function getRoot()
    {
        return $this->root;
    }
}
