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
 * A pass that might be run repeatedly.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_DependencyInjection_Compiler_RepeatedPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    /**
     * @var Boolean
     */
    private $repeat = false;

    /**
     * @var Symfony_Component_DependencyInjection_Compiler_RepeatablePassInterface[]
     */
    private $passes;

    /**
     * Constructor.
     *
     * @param Symfony_Component_DependencyInjection_Compiler_RepeatablePassInterface[] $passes An array of RepeatablePassInterface objects
     *
     * @throws Symfony_Component_DependencyInjection_Exception_InvalidArgumentException when the passes don't implement RepeatablePassInterface
     */
    public function __construct(array $passes)
    {
        foreach ($passes as $pass) {
            if (!$pass instanceof Symfony_Component_DependencyInjection_Compiler_RepeatablePassInterface) {
                throw new Symfony_Component_DependencyInjection_Exception_InvalidArgumentException('$passes must be an array of RepeatablePassInterface.');
            }

            $pass->setRepeatedPass($this);
        }

        $this->passes = $passes;
    }

    /**
     * Process the repeatable passes that run more than once.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container
     */
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $this->repeat = false;
        foreach ($this->passes as $pass) {
            $pass->process($container);
        }

        if ($this->repeat) {
            $this->process($container);
        }
    }

    /**
     * Sets if the pass should repeat
     */
    public function setRepeat()
    {
        $this->repeat = true;
    }

    /**
     * Returns the passes
     *
     * @return Symfony_Component_DependencyInjection_Compiler_RepeatablePassInterface[] An array of RepeatablePassInterface objects
     */
    public function getPasses()
    {
        return $this->passes;
    }
}
