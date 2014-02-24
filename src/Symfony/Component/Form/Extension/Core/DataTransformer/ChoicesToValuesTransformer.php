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
class Symfony_Component_Form_Extension_Core_DataTransformer_ChoicesToValuesTransformer implements Symfony_Component_Form_DataTransformerInterface
{
    private $choiceList;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Form_Extension_Core_ChoiceList_ChoiceListInterface $choiceList
     */
    public function __construct(Symfony_Component_Form_Extension_Core_ChoiceList_ChoiceListInterface $choiceList)
    {
        $this->choiceList = $choiceList;
    }

    /**
     * @param array $array
     *
     * @return array
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if the given value is not an array
     */
    public function transform($array)
    {
        if (null === $array) {
            return array();
        }

        if (!is_array($array)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($array, 'array');
        }

        return $this->choiceList->getValuesForChoices($array);
    }

    /**
     * @param array $array
     *
     * @return array
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException       if the given value is not an array
     * @throws Symfony_Component_Form_Exception_TransformationFailedException if could not find all matching choices for the given values
     */
    public function reverseTransform($array)
    {
        if (null === $array) {
            return array();
        }

        if (!is_array($array)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($array, 'array');
        }

        $choices = $this->choiceList->getChoicesForValues($array);

        if (count($choices) !== count($array)) {
            throw new Symfony_Component_Form_Exception_TransformationFailedException('Could not find all matching choices for the given values');
        }

        return $choices;
    }
}
