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
 * An implementation of BundleInterface that adds a few conventions
 * for DependencyInjection extensions and Console commands.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
abstract class Symfony_Component_HttpKernel_Bundle_Bundle extends Symfony_Component_DependencyInjection_ContainerAware implements Symfony_Component_HttpKernel_Bundle_BundleInterface
{
    protected $name;
    protected $reflected;
    protected $extension;

    /**
     * Boots the Bundle.
     */
    public function boot()
    {
    }

    /**
     * Shutdowns the Bundle.
     */
    public function shutdown()
    {
    }

    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
    }

    /**
     * Returns the bundle's container extension.
     *
     * @return Symfony_Component_DependencyInjection_Extension_ExtensionInterface|null The container extension
     *
     * @throws LogicException
     *
     * @api
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $basename = preg_replace('/Bundle$/', '', $this->getName());

            $class = $this->getNamespace().'_DependencyInjection_'.$basename.'Extension';
            if (class_exists($class)) {
                $extension = new $class();

                // check naming convention
                $expectedAlias = Symfony_Component_DependencyInjection_Container::underscore($basename);
                if ($expectedAlias != $extension->getAlias()) {
                    throw new LogicException(sprintf(
                        'The extension alias for the default extension of a '.
                        'bundle must be the underscored version of the '.
                        'bundle name ("%s" instead of "%s")',
                        $expectedAlias, $extension->getAlias()
                    ));
                }

                $this->extension = $extension;
            } else {
                $this->extension = false;
            }
        }

        if ($this->extension) {
            return $this->extension;
        }
    }

    /**
     * Gets the Bundle namespace.
     *
     * @return string The Bundle namespace
     *
     * @api
     */
    public function getNamespace()
    {
        if (null === $this->reflected) {
            $this->reflected = new ReflectionObject($this);
        }

//         return $this->reflected->getNamespaceName();
        $name = $this->reflected->getName();
        $pos = strrpos($name, '_');

        return false === $pos ? $name : substr($name, 0, $pos);
    }

    /**
     * Gets the Bundle directory path.
     *
     * @return string The Bundle absolute path
     *
     * @api
     */
    public function getPath()
    {
        if (null === $this->reflected) {
            $this->reflected = new ReflectionObject($this);
        }

        return dirname($this->reflected->getFileName());
    }

    /**
     * Returns the bundle parent name.
     *
     * @return string The Bundle parent name it overrides or null if no parent
     *
     * @api
     */
    public function getParent()
    {
        return null;
    }

    /**
     * Returns the bundle name (the class short name).
     *
     * @return string The Bundle name
     *
     * @api
     */
    final public function getName()
    {
        if (null !== $this->name) {
            return $this->name;
        }

        $name = get_class($this);
        $pos = strrpos($name, '_');

        return $this->name = false === $pos ? $name :  substr($name, $pos + 1);
    }

    /**
     * Finds and registers Commands.
     *
     * Override this method if your bundle commands do not follow the conventions:
     *
     * * Commands are in the 'Command' sub-directory
     * * Commands extend Symfony\Component\Console\Command\Command
     *
     * @param Symfony_Component_Console_Application $application An Application instance
     */
    public function registerCommands(Symfony_Component_Console_Application $application)
    {
        if (!is_dir($dir = $this->getPath().'/Command')) {
            return;
        }

        $finder = new Symfony_Component_Finder_Finder();
        $finder->files()->name('*Command.php')->in($dir);

        $prefix = $this->getNamespace().'_Command';
        foreach ($finder as $file) {
            $ns = $prefix;
            if ($relativePath = $file->getRelativePath()) {
                $ns .= '_'.strtr($relativePath, '/', '_');
            }
            $r = new ReflectionClass($ns.'_'.$file->getBasename('.php'));
            if ($r->isSubclassOf('Symfony_Component_Console_Command_Command') && !$r->isAbstract()) {
                $application->add($r->newInstance());
            }
        }
    }
}
