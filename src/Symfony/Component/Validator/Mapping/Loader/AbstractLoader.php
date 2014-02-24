<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Symfony_Component_Validator_Mapping_Loader_AbstractLoader implements Symfony_Component_Validator_Mapping_Loader_LoaderInterface
{
    /**
     * Contains all known namespaces indexed by their prefix
     * @var array
     */
    protected $namespaces;

    /**
     * Adds a namespace alias.
     *
     * @param string $alias     The alias
     * @param string $namespace The PHP namespace
     */
    protected function addNamespaceAlias($alias, $namespace)
    {
        $this->namespaces[$alias] = $namespace;
    }

    /**
     * Creates a new constraint instance for the given constraint name.
     *
     * @param string $name The constraint name. Either a constraint relative
     *                        to the default constraint namespace, or a fully
     *                        qualified class name
     * @param array $options The constraint options
     *
     * @return Constraint
     *
     * @throws Symfony_Component_Validator_Exception_MappingException If the namespace prefix is undefined
     */
    protected function newConstraint($name, $options)
    {
        if (strpos($name, '\\') !== false && class_exists($name)) {
            $className = (string) $name;
        } elseif (false !== strpos($name, '_') && class_exists($name)) {
            $className = (string) $name;
        } elseif (strpos($name, ':') !== false) {
            list($prefix, $className) = explode(':', $name, 2);

            if (!isset($this->namespaces[$prefix])) {
                throw new Symfony_Component_Validator_Exception_MappingException(sprintf('Undefined namespace prefix "%s"', $prefix));
            }

            $className = $this->namespaces[$prefix].$className;
        } else {
            $className = 'Symfony_Component_Validator_Constraints_'.$name;
        }

        return new $className($options);
    }
}
