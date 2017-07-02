<?php

namespace PB\Bundle\SuluStorageBundle\DependencyInjection\Compiler;

use League\Flysystem\FilesystemNotFoundException;
use PB\Bundle\SuluStorageBundle\Exception\AliasNotFoundException;
use PB\Bundle\SuluStorageBundle\FormatCache\PBFormatCache;
use PB\Bundle\SuluStorageBundle\Manager\PBStorageManager;
use PB\Bundle\SuluStorageBundle\Resolver\Exception\PathResolverNotDefinedException;
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
    protected $pathResolvers = [];

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
        $this->findStorageConfig($container, 'formatCache', 'format_cache');

        // Find path resolvers services
        $this->findTaggedServices($container, 'pb_sulu_storage.path_resolver', 'pathResolvers');

        // Find external url resolvers services
        $this->findTaggedServices($container, 'pb_sulu_storage.external_url_resolver', 'extUrlResolvers');

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
     * @param string|null $key
     *
     * @return $this
     */
    protected function findStorageConfig(ContainerBuilder $container, $type, $key = null)
    {
        $key = $key !== null ? $key : $type;

        if (!$container->hasParameter('pb_sulu_storage.' . $key)) {
            return $this;
        }

        $configName = $type . 'Config';
        $this->{$configName} = $container->getParameter('pb_sulu_storage.' . $key);

        return $this;
    }

    /**
     * Find tagged services.
     *
     * @param ContainerBuilder $container
     * @param string $tag
     * @param string $field
     *
     * @return $this
     */
    protected function findTaggedServices(ContainerBuilder $container, $tag, $field)
    {
        $taggedServices = $container->findTaggedServiceIds($tag);

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['alias']) || !$attributes['alias']) {
                    throw new AliasNotFoundException($tag);
                }

                $this->{$field}[$attributes['alias']] = $id;
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

        $storageManager = $this->generateStorageFilesystemManager($container, 'formatCache', 'format_cache');

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
        $replicaStorageManager = $this->generateStorageFilesystemManager($container, 'replica');

        $storageDef->setArguments([$masterStorageManager, $replicaStorageManager]);

        return $this;
    }

    /**
     * Generate storage filesystem manager.
     *
     * @param ContainerBuilder $container
     * @param string $type
     * @param null|string $key
     *
     * @return null|Definition
     */
    protected function generateStorageFilesystemManager(
        ContainerBuilder $container,
        $type = 'master',
        $key = null
    ) {
        $key = $key !== null ? $key : $type;
        $configName = $type . 'Config';

        if (!is_array($this->{$configName})) {
            return null;
        }

        $config = $this->{$configName};
        $fsServiceName = 'oneup_flysystem.' . $config['filesystem'] . '_filesystem';

        if (!$container->has($fsServiceName)) {
            throw new FilesystemNotFoundException(sprintf('Filesystem with name "%s" not found.', $config['filesystem']));
        }

        $pathResolverName = $this->findServiceNameForFilesystem($config['type'], 'pathResolvers');

        if (null === $pathResolverName) {
            throw new PathResolverNotDefinedException($config['type']);
        }

        $extUrlResolverName = $this->findServiceNameForFilesystem($config['type'], 'extUrlResolvers');

        $managerDef = new Definition(PBStorageManager::class, [
            new Reference($fsServiceName),
            new Reference($pathResolverName),
            $extUrlResolverName ? new Reference($extUrlResolverName) : null,
            isset($config['segments']) ? $config['segments'] : null,
        ]);

        $container->setDefinition('pb_sulu_storage.' . $key . '.storage_manager', $managerDef);

        return $managerDef;
    }

    /**
     * Find service name for filesystem type.
     *
     * @param string $fsType
     * @param string $field
     *
     * @return null|string
     */
    protected function findServiceNameForFilesystem($fsType, $field)
    {
        return isset($this->{$field}) && isset($this->{$field}[$fsType]) ? $this->{$field}[$fsType] : null;
    }
}