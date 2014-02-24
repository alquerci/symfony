<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Fixtures_TestExtension implements Symfony_Component_Form_FormExtensionInterface
{
    private $types = array();

    private $extensions = array();

    private $guesser;

    public function __construct(Symfony_Component_Form_FormTypeGuesserInterface $guesser)
    {
        $this->guesser = $guesser;
    }

    public function addType(Symfony_Component_Form_FormTypeInterface $type)
    {
        $this->types[$type->getName()] = $type;
    }

    public function getType($name)
    {
        return isset($this->types[$name]) ? $this->types[$name] : null;
    }

    public function hasType($name)
    {
        return isset($this->types[$name]);
    }

    public function addTypeExtension(Symfony_Component_Form_FormTypeExtensionInterface $extension)
    {
        $type = $extension->getExtendedType();

        if (!isset($this->extensions[$type])) {
            $this->extensions[$type] = array();
        }

        $this->extensions[$type][] = $extension;
    }

    public function getTypeExtensions($name)
    {
        return isset($this->extensions[$name]) ? $this->extensions[$name] : array();
    }

    public function hasTypeExtensions($name)
    {
        return isset($this->extensions[$name]);
    }

    public function getTypeGuesser()
    {
        return $this->guesser;
    }
}
