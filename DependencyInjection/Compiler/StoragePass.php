<?php

namespace PB\Bundle\SuluStorageBundle\DependencyInjection\Compiler;

use League\Flysystem\FilesystemNotFoundException;
use PB\Bundle\SuluStorageBundle\Exception\AliasNotFoundException;
use PB\Bundle\SuluStorageBundle\Exception\MasterConfigNotFoundException;
use PB\Bundle\SuluStorageBundle\FormatCache\PBFormatCache;
use PB\Bundle\SuluStorageBundle\Manager\PBStorageManager;
use PB\Bundle\SuluStorageBundle\Storage\PBStorage;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to adapt Sulu storage implementation
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class StoragePass implements CompilerPassInterface
{
    /**
     * @var array
     */
    protected $masterConfig;

    /**
     * @var array
     */
    protected $replicaConfig;

    /**
     * @var array
     */
    protected $formatCacheConfig;

    /**
     * @var array
     */
    protected $extUrlResolvers = [];

    /**
     * {@inheritdoc}
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('pb_sulu_storage.master')) {
            return;
        }

        // Find storage config
        $this->findStorageConfig($container, 'master');
        $this->findStorageConfig($container, 'replica');
        $this->findStorageConfig($container, 'format_cache');

        // Return exception if master storage config not found
        if ($this->masterConfig === null) {
            throw new MasterConfigNotFoundException('Config for master storage cannot be found.');
        }

        // Find external url resolvers services
        $this->findExternalUrlResolvers($container);

        // Set PBStorage format cache as Sulu Media Local Format Cache
        $this->overloadSuluMediaFormatCache($container);

        // Set PBStorage as Sulu Media Storage
        $this->overloadSuluMediaStorage($container);
    }

    /**
     * Find storage config.
     *
     * @param ContainerBuilder $container
     * @param string $type      master||replica
     *
     * @return $this
     */
    protected function findStorageConfig(ContainerBuilder $container, $type)
    {
        if (!$container->hasParameter('pb_sulu_storage.' . $type)) {
            return $this;
        }

        $configName = $type . 'Config';
        $this->{$configName} = $container->getParameter('pb_sulu_storage.' . $type);

        return $this;
    }

    /**
     * Find external url resolvers.
     *
     * @param ContainerBuilder $container
     *
     * @return $this
     */
    protected function findExternalUrlResolvers(ContainerBuilder $container)
    {
        $tag = 'pb_sulu_storage.external_url_resolver';
        $taggedServices = $container->findTaggedServiceIds($tag);

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['alias']) || !$attributes['alias']) {
                    throw new AliasNotFoundException($tag);
                }

                $this->extUrlResolvers[$attributes['alias']] = $id;
            }
        }

        return $this;
    }

    /**
     * Overload sulu_media.format_cache service.
     *
     * @param ContainerBuilder $container
     *
     * @return $this
     */
    protected function overloadSuluMediaFormatCache(ContainerBuilder $container)
    {
        $storageDef = $container->getDefinition('sulu_media.format_cache');
        $storageDef->setClass(PBFormatCache::class);

        $storageManager = $this->generateStorageFilesystemManager($container, 'format_cache');

        if (!$storageManager) {
            throw new MasterConfigNotFoundException('Config for format cache media storage cannot be found.');
        }

        $storageDef->setArguments([
            $storageManager,
            $container->getParameter('sulu_media.image.formats'),
            $container->getParameter('sulu_media.format_cache.media_proxy_path')
        ]);
        $storageDef->addTag('sulu_media.format_cache', ['alias' => 'pb']);

        return $this;
    }

    /**
     * Overload sulu_media.storage service.
     *
     * @param ContainerBuilder $container
     *
     * @return $this
     */
    protected function overloadSuluMediaStorage(ContainerBuilder $container)
    {
        $storageDef = $container->getDefinition('sulu_media.storage');
        $storageDef->setClass(PBStorage::class);

        $masterStorageManager = $this->generateStorageFilesystemManager($container, 'master');

        if (!$masterStorageManager) {
            throw new MasterConfigNotFoundException('Config for master storage cannot be found.');
        }

        $replicaStorageManager = $this->generateStorageFilesystemManager($container, 'replica');

        $storageDef->setArguments([$masterStorageManager, $replicaStorageManager]);

        return $this;
    }

    /**
     * Generate storage filesystem manager.
     *
     * @param ContainerBuilder $container
     * @param string $type
     *
     * @return null|Definition
     */
    protected function generateStorageFilesystemManager(
        ContainerBuilder $container,
        $type = 'master'
    ) {
        $configName = $type . 'Config';

        if (!$this->{$configName}) {
            return null;
        }

        $config = $this->{$configName};
        $fsServiceName = 'oneup_flysystem.' . $config['filesystem'] . '_filesystem';

        if (!$container->has($fsServiceName)) {
            throw new FilesystemNotFoundException(sprintf('Filesystem with name "%s" not found.', $config['filesystem']));
        }

        $extUrlResolverName = $this->findExternalUrlResolverServiceNameForFilesystem($config['type']);

        $managerDef = new Definition(PBStorageManager::class, [
            new Reference($fsServiceName),
            $extUrlResolverName ? new Reference($extUrlResolverName) : null,
            isset($config['segments']) ? $config['segments'] : null,
        ]);

        return $managerDef;
    }

    /**
     * Find external url resolver service name for filesystem type.
     *
     * @param $fsType
     *
     * @return string|null
     */
    protected function findExternalUrlResolverServiceNameForFilesystem($fsType)
    {
        return isset($this->extUrlResolvers[$fsType]) ? $this->extUrlResolvers[$fsType] : null;
    }
}