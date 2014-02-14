<?php

/*
 * (c) Alexandre Quercia <alquerci@email.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Alexandre Quercia <alquerci@email.com>
 */
class Symfony_Component_OptionsResolver_Tests_Fixtures_CounterClosure
{
    private $test;
    private $i;

    public function __construct(Symfony_Component_OptionsResolver_Tests_OptionsTest $test, &$i)
    {
        $this->test = $test;
        $this->i = &$i;
    }

    public function closureForFoo(Symfony_Component_OptionsResolver_Options $options)
    {
        $this->test->assertSame(1, $this->i);
        ++$this->i;

        // Implicitly invoke lazy option for "bam"
        $options->get('bam');
    }

    public function closureForBam(Symfony_Component_OptionsResolver_Options $options)
    {
        $this->test->assertSame(2, $this->i);
        ++$this->i;
    }

    public function normalizerForFoo(Symfony_Component_OptionsResolver_Options $options)
    {
        $this->test->assertSame(1, $this->i);
        ++$this->i;

        // Implicitly invoke normalizer for "bam"
        $options->get('bam');
    }

    public function normalizerForBam(Symfony_Component_OptionsResolver_Options $options)
    {
        $this->test->assertSame(2, $this->i);
        ++$this->i;
    }
}
