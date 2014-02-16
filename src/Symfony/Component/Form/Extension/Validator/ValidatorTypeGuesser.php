<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Extension_Validator_ValidatorTypeGuesser implements Symfony_Component_Form_FormTypeGuesserInterface
{
    private $metadataFactory;

    public function __construct(Symfony_Component_Validator_MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function guessType($class, $property)
    {
        $guesser = $this;

        return $this->guess($class, $property, array(
            new Symfony_Component_Form_Extension_Validator_ValidatorTypeGuesserClosures($guesser),
            'guessType'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function guessRequired($class, $property)
    {
        $guesser = $this;

        return $this->guess($class, $property, array(
            new Symfony_Component_Form_Extension_Validator_ValidatorTypeGuesserClosures($guesser),
            'guessRequired'
        ), false);
    }

    /**
     * {@inheritDoc}
     */
    public function guessMaxLength($class, $property)
    {
        $guesser = $this;

        return $this->guess($class, $property, array(
            new Symfony_Component_Form_Extension_Validator_ValidatorTypeGuesserClosures($guesser),
            'guessMaxLength'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function guessMinLength($class, $property)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('guessMinLength() is deprecated since version 2.1 and will be removed in 2.3.', E_USER_DEPRECATED);
    }

    /**
     * {@inheritDoc}
     */
    public function guessPattern($class, $property)
    {
        $guesser = $this;

        return $this->guess($class, $property, array(
            new Symfony_Component_Form_Extension_Validator_ValidatorTypeGuesserClosures($guesser),
            'guessPattern'
        ));
    }

    /**
     * Guesses a field class name for a given constraint
     *
     * @param Symfony_Component_Validator_Constraint $constraint The constraint to guess for
     *
     * @return Symfony_Component_Form_Guess_TypeGuess The guessed field class and options
     */
    public function guessTypeForConstraint(Symfony_Component_Validator_Constraint $constraint)
    {
        switch (get_class($constraint)) {
            case 'Symfony_Component_Validator_Constraints_Type':
                switch ($constraint->type) {
                    case 'array':
                        return new Symfony_Component_Form_Guess_TypeGuess('collection', array(), Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE);
                    case 'boolean':
                    case 'bool':
                        return new Symfony_Component_Form_Guess_TypeGuess('checkbox', array(), Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE);

                    case 'double':
                    case 'float':
                    case 'numeric':
                    case 'real':
                        return new Symfony_Component_Form_Guess_TypeGuess('number', array(), Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE);

                    case 'integer':
                    case 'int':
                    case 'long':
                        return new Symfony_Component_Form_Guess_TypeGuess('integer', array(), Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE);

                    case 'DateTime':
                        return new Symfony_Component_Form_Guess_TypeGuess('date', array(), Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE);

                    case 'string':
                        return new Symfony_Component_Form_Guess_TypeGuess('text', array(), Symfony_Component_Form_Guess_Guess::LOW_CONFIDENCE);
                }
                break;

            case 'Symfony_Component_Validator_Constraints_Country':
                return new Symfony_Component_Form_Guess_TypeGuess('country', array(), Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_Date':
                return new Symfony_Component_Form_Guess_TypeGuess('date', array('input' => 'string'), Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_DateTime':
                return new Symfony_Component_Form_Guess_TypeGuess('datetime', array('input' => 'string'), Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_Email':
                return new Symfony_Component_Form_Guess_TypeGuess('email', array(), Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_File':
            case 'Symfony_Component_Validator_Constraints_Image':
                return new Symfony_Component_Form_Guess_TypeGuess('file', array(), Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_Language':
                return new Symfony_Component_Form_Guess_TypeGuess('language', array(), Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_Locale':
                return new Symfony_Component_Form_Guess_TypeGuess('locale', array(), Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_Time':
                return new Symfony_Component_Form_Guess_TypeGuess('time', array('input' => 'string'), Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_Url':
                return new Symfony_Component_Form_Guess_TypeGuess('url', array(), Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_Ip':
                return new Symfony_Component_Form_Guess_TypeGuess('text', array(), Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_MaxLength':
            case 'Symfony_Component_Validator_Constraints_MinLength':
            case 'Symfony_Component_Validator_Constraints_Regex':
                return new Symfony_Component_Form_Guess_TypeGuess('text', array(), Symfony_Component_Form_Guess_Guess::LOW_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_Min':
            case 'Symfony_Component_Validator_Constraints_Max':
                return new Symfony_Component_Form_Guess_TypeGuess('number', array(), Symfony_Component_Form_Guess_Guess::LOW_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_MinCount':
            case 'Symfony_Component_Validator_Constraints_MaxCount':
                return new Symfony_Component_Form_Guess_TypeGuess('collection', array(), Symfony_Component_Form_Guess_Guess::LOW_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_True':
            case 'Symfony_Component_Validator_Constraints_False':
                return new Symfony_Component_Form_Guess_TypeGuess('checkbox', array(), Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE);
        }

        return null;
    }

    /**
     * Guesses whether a field is required based on the given constraint
     *
     * @param Symfony_Component_Validator_Constraint $constraint The constraint to guess for
     *
     * @return Symfony_Component_Form_Guess_Guess The guess whether the field is required
     */
    public function guessRequiredForConstraint(Symfony_Component_Validator_Constraint $constraint)
    {
        switch (get_class($constraint)) {
            case 'Symfony_Component_Validator_Constraints_NotNull':
            case 'Symfony_Component_Validator_Constraints_NotBlank':
            case 'Symfony_Component_Validator_Constraints_True':
                return new Symfony_Component_Form_Guess_ValueGuess(true, Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE);
        }

        return null;
    }

    /**
     * Guesses a field's maximum length based on the given constraint
     *
     * @param Symfony_Component_Validator_Constraint $constraint The constraint to guess for
     *
     * @return Symfony_Component_Form_Guess_Guess The guess for the maximum length
     */
    public function guessMaxLengthForConstraint(Symfony_Component_Validator_Constraint $constraint)
    {
        switch (get_class($constraint)) {
            case 'Symfony_Component_Validator_Constraints_MaxLength':
                return new Symfony_Component_Form_Guess_ValueGuess($constraint->limit, Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_Type':
                if (in_array($constraint->type, array('double', 'float', 'numeric', 'real'))) {
                        return new Symfony_Component_Form_Guess_ValueGuess(null, Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE);
                }
                break;

            case 'Symfony_Component_Validator_Constraints_Max':
                return new Symfony_Component_Form_Guess_ValueGuess(strlen((string) $constraint->limit), Symfony_Component_Form_Guess_Guess::LOW_CONFIDENCE);
        }

        return null;
    }

    /**
     * Guesses a field's pattern based on the given constraint
     *
     * @param Symfony_Component_Validator_Constraint $constraint The constraint to guess for
     *
     * @return Symfony_Component_Form_Guess_Guess The guess for the pattern
     */
    public function guessPatternForConstraint(Symfony_Component_Validator_Constraint $constraint)
    {
        switch (get_class($constraint)) {
            case 'Symfony_Component_Validator_Constraints_MinLength':
                return new Symfony_Component_Form_Guess_ValueGuess(sprintf('.{%s,}', (string) $constraint->limit), Symfony_Component_Form_Guess_Guess::LOW_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_Regex':
                $htmlPattern = $constraint->getHtmlPattern();

                if (null !== $htmlPattern) {
                    return new Symfony_Component_Form_Guess_ValueGuess($htmlPattern, Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE);
                }
                break;

            case 'Symfony_Component_Validator_Constraints_Min':
                return new Symfony_Component_Form_Guess_ValueGuess(sprintf('.{%s,}', strlen((string) $constraint->limit)), Symfony_Component_Form_Guess_Guess::LOW_CONFIDENCE);

            case 'Symfony_Component_Validator_Constraints_Type':
                if (in_array($constraint->type, array('double', 'float', 'numeric', 'real'))) {
                    return new Symfony_Component_Form_Guess_ValueGuess(null, Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE);
                }
                break;
        }

        return null;
    }

    /**
     * Iterates over the constraints of a property, executes a constraints on
     * them and returns the best guess
     *
     * @param string   $class        The class to read the constraints from
     * @param string   $property     The property for which to find constraints
     * @param callable $closure      The closure that returns a guess
     *                               for a given constraint
     * @param mixed    $defaultValue The default value assumed if no other value
     *                               can be guessed.
     *
     * @return Symfony_Component_Form_Guess_Guess The guessed value with the highest confidence
     */
    protected function guess($class, $property, $closure, $defaultValue = null)
    {
        $guesses = array();
        $classMetadata = $this->metadataFactory->getMetadataFor($class);

        if ($classMetadata->hasMemberMetadatas($property)) {
            $memberMetadatas = $classMetadata->getMemberMetadatas($property);

            foreach ($memberMetadatas as $memberMetadata) {
                $constraints = $memberMetadata->getConstraints();

                foreach ($constraints as $constraint) {
                    if ($guess = call_user_func($closure, $constraint)) {
                        $guesses[] = $guess;
                    }
                }
            }

            if (null !== $defaultValue) {
                $guesses[] = new Symfony_Component_Form_Guess_ValueGuess($defaultValue, Symfony_Component_Form_Guess_Guess::LOW_CONFIDENCE);
            }
        }

        return Symfony_Component_Form_Guess_Guess::getBestGuess($guesses);
    }
}

class Symfony_Component_Form_Extension_Validator_ValidatorTypeGuesserClosures
{
    private $guesser;

    public function __construct($guesser)
    {
        $this->guesser = $guesser;
    }

    public function guessType(Symfony_Component_Validator_Constraint $constraint)
    {
        return $this->guesser->guessTypeForConstraint($constraint);
    }

    public function guessRequired(Symfony_Component_Validator_Constraint $constraint)
    {
        return $this->guesser->guessRequiredForConstraint($constraint);
        // If we don't find any constraint telling otherwise, we can assume
        // that a field is not required (with LOW_CONFIDENCE)
    }

    public function guessMaxLength(Symfony_Component_Validator_Constraint $constraint)
    {
        return $this->guesser->guessMaxLengthForConstraint($constraint);
    }

    public function guessPattern(Symfony_Component_Validator_Constraint $constraint)
    {
        return $this->guesser->guessPatternForConstraint($constraint);
    }
}
