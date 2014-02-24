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
interface Symfony_Component_Form_Extension_Validator_ViolationMapper_ViolationMapperInterface
{
    /**
     * Maps a constraint violation to a form in the form tree under
     * the given form.
     *
     * @param Symfony_Component_Validator_ConstraintViolation $violation The violation to map.
     * @param Symfony_Component_Form_FormInterface       $form      The root form of the tree
     *                                       to map it to.
     * @param Boolean             $allowNonSynchronized Whether to allow
     *                                       mapping to non-synchronized forms.
     */
    public function mapViolation(Symfony_Component_Validator_ConstraintViolation $violation, Symfony_Component_Form_FormInterface $form, $allowNonSynchronized = false);
}
