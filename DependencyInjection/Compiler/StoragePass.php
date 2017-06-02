<?php

namespace PB\Bundle\SuluStorageBundle\DependencyInjection\Compiler;

use League\Flysystem\FilesystemNotFoundException;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManager;
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
     * {@inheritdoc}
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('pb_sulu_storage.master') ||
            !$container->has('pb_sulu_storage.storage')
        ) {
            return;
        }

        $this->defineStorageService($container);

        // Set PBStorage as Sulu Media Storage
        $this->overloadSuluMediaManager($container);
    }

    /**
     * Define PB storage service.
     *
     * @param ContainerBuilder $container
     *
     * @return $this
     */
    protected function defineStorageService(ContainerBuilder $container)
    {
        $this->setStorageFilesystem($container, 'master');
        $this->setStorageFilesystem($container, 'replica');

        return $this;
    }

    /**
     * Set storage filesystem.
     *
     * @param ContainerBuilder $container
     * @param string $type          master||replica
     *
     * @return $this
     */
    protected function setStorageFilesystem(ContainerBuilder $container, $type = 'master')
    {
        if (!$container->hasParameter('pb_sulu_storage.' . $type)) {
            return $this;
        }

        $config = $container->getParameter('pb_sulu_storage.' . $type);
        $fsServiceName = 'oneup_flysystem.' . $config['filesystem'] . '_filesystem';

        if (!$container->has($fsServiceName)) {
            throw new FilesystemNotFoundException(sprintf('Filesystem with name "%s" not found.', $config['filesystem']));
        }

        $storageDef = $container->findDefinition('pb_sulu_storage.storage');

        switch ($type) {
            case 'replica':
                $methodCallName = 'setReplicaFilesystem';
                break;
            case 'master':
            default:
                $methodCallName = 'setMasterFilesystem';
                $storageDef->addMethodCall('setSegments', [$config['segments']]);
        }

        $storageDef->addMethodCall($methodCallName, [new Reference($fsServiceName)]);

        return $this;
    }

    /**
     * Overload SuluMediaManager to use PBSuluStorageBundle Storage and Format Cache
     *
     * @param ContainerBuilder $container
     *
     * @return $this
     */
    protected function overloadSuluMediaManager(ContainerBuilder $container)
    {
        $managerDef = $container->getDefinition('sulu_media.media_manager');
        $managerDef->setArguments([
            new Reference('sulu.repository.media'),
            new Reference('sulu_media.collection_repository'),
            new Reference('sulu.repository.user'),
            new Reference('sulu.repository.category'),
            new Reference('doctrine.orm.entity_manager'),
            new Reference('pb_sulu_storage.storage'),
            new Reference('sulu_media.file_validator'),
            new Reference('sulu_media.format_manager'),
            new Reference('sulu_tag.tag_manager'),
            new Reference('sulu_media.type_manager'),
            new Reference('sulu.content.path_cleaner'),
            new Reference('security.token_storage', ContainerBuilder::NULL_ON_INVALID_REFERENCE),
            new Reference('sulu_security.security_checker', ContainerBuilder::NULL_ON_INVALID_REFERENCE),
            new Reference('dubture_ffmpeg.ffprobe'),
            $container->getParameter('sulu_security.permissions'),
            $container->getParameter('sulu_media.media_manager.media_download_path'),
            $container->getParameter('sulu_media.media.max_file_size'),
        ]);

        return $this;

//        var_dump('overload');exit;

//        <service id="sulu_media.media_manager" class="%sulu_media.media_manager.class%">
//            <argument type="service" id="sulu.repository.media" />
//            <argument type="service" id="sulu_media.collection_repository" />
//            <argument type="service" id="sulu.repository.user" />
//            <argument type="service" id="sulu.repository.category"/>
//            <argument type="service" id="doctrine.orm.entity_manager" />
//            <argument type="service" id="sulu_media.storage" />
//            <argument type="service" id="sulu_media.file_validator" />
//            <argument type="service" id="sulu_media.format_manager" />
//            <argument type="service" id="sulu_tag.tag_manager" />
//            <argument type="service" id="sulu_media.type_manager" />
//            <argument type="service" id="sulu.content.path_cleaner" />
//            <argument type="service" id="security.token_storage" on-invalid="null" />
//            <argument type="service" id="sulu_security.security_checker" on-invalid="null" />
//            <argument type="service" id="dubture_ffmpeg.ffprobe" />
//            <argument>%sulu_security.permissions%</argument>
//            <argument type="string">%sulu_media.media_manager.media_download_path%</argument>
//            <argument>%sulu_media.media.max_file_size%</argument>
//        </service>
    }
}