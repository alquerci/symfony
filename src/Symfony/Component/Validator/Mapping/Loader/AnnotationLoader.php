<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Mapping_Loader_AnnotationLoader implements Symfony_Component_Validator_Mapping_Loader_LoaderInterface
{
    protected $reader;

    public function __construct(Doctrine_Common_Annotations_Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritDoc}
     */
    public function loadClassMetadata(Symfony_Component_Validator_Mapping_ClassMetadata $metadata)
    {
        $reflClass = $metadata->getReflectionClass();
        $className = $reflClass->name;
        $loaded = false;

        foreach ($this->reader->getClassAnnotations($reflClass) as $constraint) {
            if ($constraint instanceof Symfony_Component_Validator_Constraints_GroupSequence) {
                $metadata->setGroupSequence($constraint->groups);
            } elseif ($constraint instanceof Symfony_Component_Validator_Constraints_GroupSequenceProvider) {
                $metadata->setGroupSequenceProvider(true);
            } elseif ($constraint instanceof Symfony_Component_Validator_Constraint) {
                $metadata->addConstraint($constraint);
            }

            $loaded = true;
        }

        foreach ($reflClass->getProperties() as $property) {
            if ($property->getDeclaringClass()->name == $className) {
                foreach ($this->reader->getPropertyAnnotations($property) as $constraint) {
                    if ($constraint instanceof Symfony_Component_Validator_Constraint) {
                        $metadata->addPropertyConstraint($property->name, $constraint);
                    }

                    $loaded = true;
                }
            }
        }

        foreach ($reflClass->getMethods() as $method) {
            if ($method->getDeclaringClass()->name ==  $className) {
                foreach ($this->reader->getMethodAnnotations($method) as $constraint) {
                    if ($constraint instanceof Symfony_Component_Validator_Constraint) {
                        if (preg_match('/^(get|is)(.+)$/i', $method->name, $matches)) {
                            $metadata->addGetterConstraint(lcfirst($matches[2]), $constraint);
                        } else {
                            throw new Symfony_Component_Validator_Exception_MappingException(sprintf('The constraint on "%s::%s" cannot be added. Constraints can only be added on methods beginning with "get" or "is".', $className, $method->name));
                        }
                    }

                    $loaded = true;
                }
            }
        }

        return $loaded;
    }
}
