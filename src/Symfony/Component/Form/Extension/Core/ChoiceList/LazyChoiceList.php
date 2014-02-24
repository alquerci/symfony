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
 * A choice list that is loaded lazily
 *
 * This list loads itself as soon as any of the getters is accessed for the
 * first time. You should implement loadChoiceList() in your child classes,
 * which should return a ChoiceListInterface instance.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class Symfony_Component_Form_Extension_Core_ChoiceList_LazyChoiceList implements Symfony_Component_Form_Extension_Core_ChoiceList_ChoiceListInterface
{
    /**
     * The loaded choice list
     *
     * @var Symfony_Component_Form_Extension_Core_ChoiceList_ChoiceListInterface
     */
    private $choiceList;

    /**
     * {@inheritdoc}
     */
    public function getChoices()
    {
        if (!$this->choiceList) {
            $this->load();
        }

        return $this->choiceList->getChoices();
    }

    /**
     * {@inheritdoc}
     */
    public function getValues()
    {
        if (!$this->choiceList) {
            $this->load();
        }

        return $this->choiceList->getValues();
    }

    /**
     * {@inheritdoc}
     */
    public function getPreferredViews()
    {
        if (!$this->choiceList) {
            $this->load();
        }

        return $this->choiceList->getPreferredViews();
    }

    /**
     * {@inheritdoc}
     */
    public function getRemainingViews()
    {
        if (!$this->choiceList) {
            $this->load();
        }

        return $this->choiceList->getRemainingViews();
    }

    /**
     * {@inheritdoc}
     */
    public function getChoicesForValues(array $values)
    {
        if (!$this->choiceList) {
            $this->load();
        }

        return $this->choiceList->getChoicesForValues($values);
    }

    /**
     * {@inheritdoc}
     */
    public function getValuesForChoices(array $choices)
    {
        if (!$this->choiceList) {
            $this->load();
        }

        return $this->choiceList->getValuesForChoices($choices);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndicesForChoices(array $choices)
    {
        if (!$this->choiceList) {
            $this->load();
        }

        return $this->choiceList->getIndicesForChoices($choices);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndicesForValues(array $values)
    {
        if (!$this->choiceList) {
            $this->load();
        }

        return $this->choiceList->getIndicesForValues($values);
    }

    /**
     * Loads the choice list
     *
     * Should be implemented by child classes.
     *
     * @return Symfony_Component_Form_Extension_Core_ChoiceList_ChoiceListInterface The loaded choice list
     */
    abstract protected function loadChoiceList();

    private function load()
    {
        $choiceList = $this->loadChoiceList();

        if (!$choiceList instanceof Symfony_Component_Form_Extension_Core_ChoiceList_ChoiceListInterface) {
            throw new Symfony_Component_Form_Exception_Exception('loadChoiceList() should return a ChoiceListInterface instance. Got ' . gettype($choiceList));
        }

        $this->choiceList = $choiceList;
    }
}
