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
 * Used to format logging messages during the compilation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_DependencyInjection_Compiler_LoggingFormatter
{
    public function formatRemoveService(Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface $pass, $id, $reason)
    {
        return $this->format($pass, sprintf('Removed service "%s"; reason: %s', $id, $reason));
    }

    public function formatInlineService(Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface $pass, $id, $target)
    {
        return $this->format($pass, sprintf('Inlined service "%s" to "%s".', $id, $target));
    }

    public function formatUpdateReference(Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface $pass, $serviceId, $oldDestId, $newDestId)
    {
        return $this->format($pass, sprintf('Changed reference of service "%s" previously pointing to "%s" to "%s".', $serviceId, $oldDestId, $newDestId));
    }

    public function formatResolveInheritance(Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface $pass, $childId, $parentId)
    {
        return $this->format($pass, sprintf('Resolving inheritance for "%s" (parent: %s).', $childId, $parentId));
    }

    public function format(Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface $pass, $message)
    {
        return sprintf('%s: %s', get_class($pass), $message);
    }
}
