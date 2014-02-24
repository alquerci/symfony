<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Mapping_Loader_StaticMethodLoader implements Symfony_Component_Validator_Mapping_Loader_LoaderInterface
{
    protected $methodName;

    public function __construct($methodName = 'loadValidatorMetadata')
    {
        $this->methodName = $methodName;
    }

    /**
     * {@inheritDoc}
     */
    public function loadClassMetadata(Symfony_Component_Validator_Mapping_ClassMetadata $metadata)
    {
        /** @var ReflectionClass $reflClass */
        $reflClass = $metadata->getReflectionClass();

        if (!$reflClass->isInterface() && $reflClass->hasMethod($this->methodName)) {
            $reflMethod = $reflClass->getMethod($this->methodName);

            if (!$reflMethod->isStatic()) {
                throw new Symfony_Component_Validator_Exception_MappingException(sprintf('The method %s::%s should be static', $reflClass->name, $this->methodName));
            }

            if ($reflMethod->getDeclaringClass()->name != $reflClass->name) {
                return false;
            }

            $reflMethod->invoke(null, $metadata);

            return true;
        }

        return false;
    }
}
