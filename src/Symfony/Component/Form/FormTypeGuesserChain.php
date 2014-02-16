<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_FormTypeGuesserChain implements Symfony_Component_Form_FormTypeGuesserInterface
{
    private $guessers = array();

    /**
     * Constructor.
     *
     * @param array $guessers Guessers as instances of FormTypeGuesserInterface
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if any guesser does not implement FormTypeGuesserInterface
     */
    public function __construct(array $guessers)
    {
        foreach ($guessers as $guesser) {
            if (!$guesser instanceof Symfony_Component_Form_FormTypeGuesserInterface) {
                throw new Symfony_Component_Form_Exception_UnexpectedTypeException($guesser, 'Symfony_Component_Form_FormTypeGuesserInterface');
            }

            if ($guesser instanceof self) {
                $this->guessers = array_merge($this->guessers, $guesser->guessers);
            } else {
                $this->guessers[] = $guesser;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function guessType($class, $property)
    {
        return $this->guess(array(
            new Symfony_Component_Form_FormTypeGuesserChainClosures($class, $property),
            'guessType'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function guessRequired($class, $property)
    {
        return $this->guess(array(
            new Symfony_Component_Form_FormTypeGuesserChainClosures($class, $property),
            'guessRequired'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function guessMaxLength($class, $property)
    {
        return $this->guess(array(
            new Symfony_Component_Form_FormTypeGuesserChainClosures($class, $property),
            'guessMaxLength'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function guessMinLength($class, $property)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('guessMinLength() is deprecated since version 2.1 and will be removed in 2.3.', E_USER_DEPRECATED);

        return $this->guess(array(
            new Symfony_Component_Form_FormTypeGuesserChainClosures($class, $property),
            'guessMinLength'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function guessPattern($class, $property)
    {
        return $this->guess(array(
            new Symfony_Component_Form_FormTypeGuesserChainClosures($class, $property),
            'guessPattern'
        ));
    }

    /**
     * Executes a closure for each guesser and returns the best guess from the
     * return values
     *
     * @param callable $closure The closure to execute. Accepts a guesser
     *                            as argument and should return a Symfony_Component_Form_Guess_Guess instance
     *
     * @return Symfony_Component_Form_Guess_Guess The guess with the highest confidence
     */
    private function guess($closure)
    {
        $guesses = array();

        foreach ($this->guessers as $guesser) {
            if ($guess = call_user_func($closure, $guesser)) {
                $guesses[] = $guess;
            }
        }

        return Symfony_Component_Form_Guess_Guess::getBestGuess($guesses);
    }
}

class Symfony_Component_Form_FormTypeGuesserChainClosures
{
    private $class;
    private $property;

    public function __construct($class, $property)
    {
        $this->class = $class;
        $this->property = $property;
    }

    public function guessType($guesser)
    {
        return $guesser->guessType($this->class, $this->property);
    }

    public function guessRequired($guesser)
    {
        return $guesser->guessRequired($this->class, $this->property);
    }

    public function guessMaxLength($guesser)
    {
        return $guesser->guessMaxLength($this->class, $this->property);
    }

    public function guessMinLength($guesser)
    {
        return $guesser->guessMinLength($this->class, $this->property);
    }

    public function guessPattern($guesser)
    {
        return $guesser->guessPattern($this->class, $this->property);
    }
}
