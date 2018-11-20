<?php

use Symfony\Component\HttpKernel;
use Symfony\Component\Kernel;

// Naïve implementation of the BC layer

class_alias(Kernel\KernelInterface::class, HttpKernel\KernelInterface::class);
class_alias(Kernel\RebootableInterface::class, HttpKernel\RebootableInterface::class);

class_alias(Kernel\Bundle\BundleInterface::class, HttpKernel\Bundle\BundleInterface::class);
class_alias(Kernel\Bundle\Bundle::class, HttpKernel\Bundle\Bundle::class);

class_alias(Kernel\CacheClearer\CacheClearerInterface::class, HttpKernel\CacheClearer\CacheClearerInterface::class);
class_alias(Kernel\CacheClearer\ChainCacheClearer::class, HttpKernel\CacheClearer\ChainCacheClearer::class);
class_alias(Kernel\CacheClearer\Psr6CacheClearer::class, HttpKernel\CacheClearer\Psr6CacheClearer::class);

class_alias(Kernel\Config\FileLocator::class, HttpKernel\Config\FileLocator::class);

class_alias(Kernel\CacheWarmer\CacheWarmerInterface::class, HttpKernel\CacheWarmer\CacheWarmerInterface::class);
class_alias(Kernel\CacheWarmer\CacheWarmer::class, HttpKernel\CacheWarmer\CacheWarmer::class);
class_alias(Kernel\CacheWarmer\CacheWarmerAggregate::class, HttpKernel\CacheWarmer\CacheWarmerAggregate::class);

class_alias(Kernel\DependencyInjection\AddAnnotatedClassesToCachePass::class, HttpKernel\DependencyInjection\AddAnnotatedClassesToCachePass::class);
class_alias(Kernel\DependencyInjection\ConfigurableExtension::class, HttpKernel\DependencyInjection\ConfigurableExtension::class);
class_alias(Kernel\DependencyInjection\Extension::class, HttpKernel\DependencyInjection\Extension::class);
class_alias(Kernel\DependencyInjection\LoggerPass::class, HttpKernel\DependencyInjection\LoggerPass::class);
class_alias(Kernel\DependencyInjection\MergeExtensionConfigurationPass::class, HttpKernel\DependencyInjection\MergeExtensionConfigurationPass::class);
class_alias(Kernel\DependencyInjection\ResettableServicePass::class, HttpKernel\DependencyInjection\ResettableServicePass::class);
class_alias(Kernel\DependencyInjection\ServicesResetter::class, HttpKernel\DependencyInjection\ServicesResetter::class);

class_alias(Kernel\EventListener\DumpListener::class, HttpKernel\EventListener\DumpListener::class);

class_alias(Kernel\Log\Logger::class, HttpKernel\Log\Logger::class);
