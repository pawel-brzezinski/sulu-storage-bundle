<?php

namespace PB\Bundle\SuluStorageBundle\DependencyInjection\Compiler;

use PB\Bundle\SuluStorageBundle\DependencyInjection\PBSuluStorageExtension;
use PB\Bundle\SuluStorageBundle\Flysystem\Exception\FilesystemNotFoundException;
use PB\Bundle\SuluStorageBundle\Media\FormatCache\PBFormatCache;
use PB\Bundle\SuluStorageBundle\Media\FormatManager\PBFormatManager;
use PB\Bundle\SuluStorageBundle\Media\Resolver\FileResolver;
use PB\Bundle\SuluStorageBundle\Media\Storage\PBStorage;
use PB\Bundle\SuluStorageBundle\Provider\Filesystem\FlysystemProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Sulu storage compiler pass.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class StoragePass implements CompilerPassInterface
{
    const FILE_OVERLAY_SERVICE_ID = 'pb_sulu_storage.file.overlay';
    const STORAGE_FILE_RESOLVER_SERVICE_ID = 'pb_sulu_storage.storage_file_resolver';
    const STORAGE_FILESYSTEM_PROVIDER_SERVICE_ID = 'pb_sulu_storage.storage_filesystem_provider';
    const FORMAT_CACHE_FILE_RESOLVER_SERVICE_ID = 'pb_sulu_storage.format_cache_file_resolver';
    const FORMAT_CACHE_FILESYSTEM_PROVIDER_SERVICE_ID = 'pb_sulu_storage.format_cache_filesystem_provider';
    const MATH_OVERLAY_SERVICE_ID = 'pb_sulu_storage.math.overlay';
    const SULU_CONTENT_PATH_CLEANER_SERVICE_ID = 'sulu.content.path_cleaner';
    const SULU_MEDIA_FORMAT_CACHE_SERVICE_ID = 'sulu_media.format_cache';
    const SULU_MEDIA_FORMAT_MANAGER_SERVICE_ID = 'sulu_media.format_manager';
    const SULU_MEDIA_STORAGE_SERVICE_ID = 'sulu_media.storage';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter(PBSuluStorageExtension::BUNDLE_CONFIG_PARAM)) {
            return;
        }

        $config = $container->getParameter(PBSuluStorageExtension::BUNDLE_CONFIG_PARAM);

        $this->defineFilesystemProviderService($container, $config);
        $this->overloadSuluMediaStorage($container, $config);
        $this->overloadSuluMediaFormatCache($container, $config);
        $this->overloadSuluMediaFormatManager($container, $config);
    }

    /**
     * Define filesystem provider service.
     *
     * @param ContainerBuilder $container
     * @param array $config
     *
     * @throws FilesystemNotFoundException
     */
    private function defineFilesystemProviderService(ContainerBuilder $container, array $config)
    {
        $provider = $config['provider'];

        if ('flysystem' === $provider) {
            $storageFsId = $config['flysystem']['filesystem']['storage'];
            $this->defineFlysystemFilesystemProviderService(
                $container,
                self::STORAGE_FILESYSTEM_PROVIDER_SERVICE_ID,
                $storageFsId
            );
            $this->defineFileResolverService(
                $container,
                self::STORAGE_FILE_RESOLVER_SERVICE_ID,
                self::STORAGE_FILESYSTEM_PROVIDER_SERVICE_ID,
                $config['logger']
            );

            $formatCacheFsId = $config['flysystem']['filesystem']['format_cache'];
            $this->defineFlysystemFilesystemProviderService(
                $container,
                self::FORMAT_CACHE_FILESYSTEM_PROVIDER_SERVICE_ID,
                $formatCacheFsId
            );
            $this->defineFileResolverService(
                $container,
                self::FORMAT_CACHE_FILE_RESOLVER_SERVICE_ID,
                self::FORMAT_CACHE_FILESYSTEM_PROVIDER_SERVICE_ID,
                $config['logger']
            );

        }
    }

    /**
     * Define Flysystem filesystem provider service.
     *
     * @param ContainerBuilder $container
     * @param string $id
     * @param string $flysystemFilesystemId
     *
     * @throws FilesystemNotFoundException
     */
    private function defineFlysystemFilesystemProviderService(ContainerBuilder $container, string $id, string $flysystemFilesystemId) {
        // If configured storage filesystem is not an alias to service create standard oneup flysystem service id
        if (false === $container->has($flysystemFilesystemId)) {
            $flysystemFilesystemId = sprintf('oneup_flysystem.%s_filesystem', $flysystemFilesystemId);
        }

        if (false === $container->has($flysystemFilesystemId)) {
            throw new FilesystemNotFoundException($flysystemFilesystemId);
        }

        $fspDef = new Definition(FlysystemProvider::class, [
            new Reference($flysystemFilesystemId),
        ]);

        $container->setDefinition($id, $fspDef);
    }

    /**
     * Define file resolver service.
     *
     * @param ContainerBuilder $container
     * @param string $id
     * @param string $filesystemProviderId
     */
    private function defineFileResolverService(ContainerBuilder $container, string $id, string $filesystemProviderId, string $loggerId)
    {
        $frDef = new Definition(FileResolver::class, [
            new Reference($filesystemProviderId),
            new Reference(self::SULU_CONTENT_PATH_CLEANER_SERVICE_ID),
            new Reference($loggerId),
        ]);

        $container->setDefinition($id, $frDef);
    }

    /**
     * Overload Sulu Media storage service.
     *
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function overloadSuluMediaStorage(ContainerBuilder $container, array $config)
    {
        $def = $container->getDefinition(self::SULU_MEDIA_STORAGE_SERVICE_ID);
        $def->setClass(PBStorage::class)->setArguments([
            new Reference(self::STORAGE_FILESYSTEM_PROVIDER_SERVICE_ID),
            new Reference(self::STORAGE_FILE_RESOLVER_SERVICE_ID),
            new Reference(self::FILE_OVERLAY_SERVICE_ID),
            new Reference(self::MATH_OVERLAY_SERVICE_ID),
            $config['segments'],
            new Reference($config['logger'])
        ]);
    }

    /**
     * Overload Sulu Media format cache service.
     *
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function overloadSuluMediaFormatCache(ContainerBuilder $container, array $config)
    {
        $def = $container->getDefinition(self::SULU_MEDIA_FORMAT_CACHE_SERVICE_ID);
        $def->setClass(PBFormatCache::class)->setArguments([
            new Reference(self::FORMAT_CACHE_FILESYSTEM_PROVIDER_SERVICE_ID),
            new Reference(self::FORMAT_CACHE_FILE_RESOLVER_SERVICE_ID),
            $container->getParameter('sulu_media.format_cache.media_proxy_path'),
            $config['segments'],
            $container->getParameter('sulu_media.image.formats'),
        ]);
    }

    /**
     * Overload Sulu Media format manager service.
     *
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function overloadSuluMediaFormatManager(ContainerBuilder $container, array $config)
    {
        $def = $container->getDefinition(self::SULU_MEDIA_FORMAT_MANAGER_SERVICE_ID);
        $args = $def->getArguments();
        $args[] = new Reference($config['logger']);

        $def->setClass(PBFormatManager::class)->setArguments($args);
    }
}
