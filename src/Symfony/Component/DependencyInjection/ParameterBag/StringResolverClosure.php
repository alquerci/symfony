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
 * @author Alexandre Quercia <alquerci@email.com>
 */
class Symfony_Component_DependencyInjection_ParameterBag_StringResolverClosure
{
    /**
     * @var Symfony_Component_DependencyInjection_ParameterBag_ParameterBagInterface
     */
    private $parameterBag;
    private $value;
    private $resolving = array();

    /**
     * @param Symfony_Component_DependencyInjection_ParameterBag_ParameterBagInterface $parameterBag
     * @param string $value
     * @param array  $resolving
     */
    public function __construct(Symfony_Component_DependencyInjection_ParameterBag_ParameterBagInterface $parameterBag, $value, array $resolving)
    {
        $this->parameterBag = $parameterBag;
        $this->value = $value;
        $this->resolving = $resolving;
    }

    /**
     * @param array $match
     *
     * @return string
     *
     * @throws Symfony_Component_DependencyInjection_Exception_ParameterCircularReferenceException
     * @throws Symfony_Component_DependencyInjection_Exception_RuntimeException
     */
    public function __invoke(array $match)
    {
        // skip %%
        if (!isset($match[1])) {
            return '%%';
        }

        $key = strtolower($match[1]);
        if (isset($this->resolving[$key])) {
            throw new Symfony_Component_DependencyInjection_Exception_ParameterCircularReferenceException(array_keys($this->resolving));
        }

        $resolved = $this->parameterBag->get($key);

        if (!is_string($resolved) && !is_numeric($resolved)) {
            throw new Symfony_Component_DependencyInjection_Exception_RuntimeException(sprintf('A string value must be composed of strings and/or numbers, but found parameter "%s" of type %s inside string value "%s".', $key, gettype($resolved), $this->value));
        }

        $resolved = (string) $resolved;
        $resolving = $this->resolving;
        $resolving[$key] = true;

        return $this->parameterBag->isResolved() ? $resolved : $this->parameterBag->resolveString($resolved, $resolving);
    }
}
