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
class Symfony_Component_Form_Extension_Core_DataTransformer_ChoiceToBooleanArrayTransformer implements Symfony_Component_Form_DataTransformerInterface
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
     * Transforms a single choice to a format appropriate for the nested
     * checkboxes/radio buttons.
     *
     * The result is an array with the options as keys and true/false as values,
     * depending on whether a given option is selected. If this field is rendered
     * as select tag, the value is not modified.
     *
     * @param mixed $choice An array if "multiple" is set to true, a scalar
     *                       value otherwise.
     *
     * @return mixed An array
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if the given value is not scalar
     * @throws Symfony_Component_Form_Exception_TransformationFailedException if the choices can not be retrieved
     */
    public function transform($choice)
    {
        try {
            $values = $this->choiceList->getValues();
        } catch (Exception $e) {
            throw new Symfony_Component_Form_Exception_TransformationFailedException('Can not get the choice list', $e->getCode(), $e);
        }

        $index = current($this->choiceList->getIndicesForChoices(array($choice)));

        foreach ($values as $i => $value) {
            $values[$i] = $i === $index;
        }

        return $values;
    }

    /**
     * Transforms a checkbox/radio button array to a single choice.
     *
     * The input value is an array with the choices as keys and true/false as
     * values, depending on whether a given choice is selected. The output
     * is the selected choice.
     *
     * @param array $values An array of values
     *
     * @return mixed A scalar value
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException       if the given value is not an array
     * @throws Symfony_Component_Form_Exception_TransformationFailedException if the recuperation of the choices fails or
     *                                       if some choice can't be found
     */
    public function reverseTransform($values)
    {
        if (!is_array($values)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($values, 'array');
        }

        try {
            $choices = $this->choiceList->getChoices();
        } catch (Exception $e) {
            throw new Symfony_Component_Form_Exception_TransformationFailedException('Can not get the choice list', $e->getCode(), $e);
        }

        foreach ($values as $i => $selected) {
            if ($selected) {
                if (isset($choices[$i])) {
                    return $choices[$i] === '' ? null : $choices[$i];
                } else {
                    throw new Symfony_Component_Form_Exception_TransformationFailedException('The choice "' . $i . '" does not exist');
                }
            }
        }

        return null;
    }
}
