<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Guess_TestGuess extends Symfony_Component_Form_Guess_Guess {}

class Symfony_Component_Form_Tests_Guess_GuessTest extends PHPUnit_Framework_TestCase
{
    public function testGetBestGuessReturnsGuessWithHighestConfidence()
    {
        $guess1 = new Symfony_Component_Form_Tests_Guess_TestGuess(Symfony_Component_Form_Guess_Guess::MEDIUM_CONFIDENCE);
        $guess2 = new Symfony_Component_Form_Tests_Guess_TestGuess(Symfony_Component_Form_Guess_Guess::LOW_CONFIDENCE);
        $guess3 = new Symfony_Component_Form_Tests_Guess_TestGuess(Symfony_Component_Form_Guess_Guess::HIGH_CONFIDENCE);

        $this->assertSame($guess3, Symfony_Component_Form_Guess_Guess::getBestGuess(array($guess1, $guess2, $guess3)));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGuessExpectsValidConfidence()
    {
        new Symfony_Component_Form_Tests_Guess_TestGuess(5);
    }
}
