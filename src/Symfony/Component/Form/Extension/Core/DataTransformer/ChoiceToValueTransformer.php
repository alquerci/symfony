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
class Symfony_Component_Form_Extension_Core_DataTransformer_ChoiceToValueTransformer implements Symfony_Component_Form_DataTransformerInterface
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

    public function transform($choice)
    {
        return (string) current($this->choiceList->getValuesForChoices(array($choice)));
    }

    public function reverseTransform($value)
    {
        if (null !== $value && !is_scalar($value)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($value, 'scalar');
        }

        // These are now valid ChoiceList values, so we can return null
        // right away
        if ('' === $value || null === $value) {
            return null;
        }

        $choices = $this->choiceList->getChoicesForValues(array($value));

        if (1 !== count($choices)) {
            throw new Symfony_Component_Form_Exception_TransformationFailedException('The choice "' . $value . '" does not exist or is not unique');
        }

        $choice = current($choices);

        return '' === $choice ? null : $choice;
    }
}
