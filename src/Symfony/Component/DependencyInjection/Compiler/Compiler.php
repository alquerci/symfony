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
 * This class is used to remove circular dependencies between individual passes.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * @api
 */
class Symfony_Component_DependencyInjection_Compiler_Compiler
{
    private $passConfig;
    private $log;
    private $loggingFormatter;
    private $serviceReferenceGraph;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->passConfig = new Symfony_Component_DependencyInjection_Compiler_PassConfig();
        $this->serviceReferenceGraph = new Symfony_Component_DependencyInjection_Compiler_ServiceReferenceGraph();
        $this->loggingFormatter = new Symfony_Component_DependencyInjection_Compiler_LoggingFormatter();
        $this->log = array();
    }

    /**
     * Returns the PassConfig.
     *
     * @return Symfony_Component_DependencyInjection_Compiler_PassConfig The PassConfig instance
     *
     * @api
     */
    public function getPassConfig()
    {
        return $this->passConfig;
    }

    /**
     * Returns the ServiceReferenceGraph.
     *
     * @return Symfony_Component_DependencyInjection_Compiler_ServiceReferenceGraph The ServiceReferenceGraph instance
     *
     * @api
     */
    public function getServiceReferenceGraph()
    {
        return $this->serviceReferenceGraph;
    }

    /**
     * Returns the logging formatter which can be used by compilation passes.
     *
     * @return Symfony_Component_DependencyInjection_Compiler_LoggingFormatter
     */
    public function getLoggingFormatter()
    {
        return $this->loggingFormatter;
    }

    /**
     * Adds a pass to the PassConfig.
     *
     * @param Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface $pass A compiler pass
     * @param string                $type The type of the pass
     *
     * @api
     */
    public function addPass(Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface $pass, $type = Symfony_Component_DependencyInjection_Compiler_PassConfig::TYPE_BEFORE_OPTIMIZATION)
    {
        $this->passConfig->addPass($pass, $type);
    }

    /**
     * Adds a log message.
     *
     * @param string $string The log message
     */
    public function addLogMessage($string)
    {
        $this->log[] = $string;
    }

    /**
     * Returns the log.
     *
     * @return array Log array
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Run the Compiler and process all Passes.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container
     *
     * @api
     */
    public function compile(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        foreach ($this->passConfig->getPasses() as $pass) {
            $pass->process($container);
        }
    }
}
