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
 * Reverses a transformer
 *
 * When the transform() method is called, the reversed transformer's
 * reverseTransform() method is called and vice versa.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_ReversedTransformer implements Symfony_Component_Form_DataTransformerInterface
{
    /**
     * The reversed transformer
     * @var Symfony_Component_Form_DataTransformerInterface
     */
    protected $reversedTransformer;

    /**
     * Reverses this transformer
     *
     * @param Symfony_Component_Form_DataTransformerInterface $reversedTransformer
     */
    public function __construct(Symfony_Component_Form_DataTransformerInterface $reversedTransformer)
    {
        $this->reversedTransformer = $reversedTransformer;
    }

    /**
     * {@inheritDoc}
     */
    public function transform($value)
    {
        return $this->reversedTransformer->reverseTransform($value);
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value)
    {
        return $this->reversedTransformer->transform($value);
    }
}
