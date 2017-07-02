<?php

namespace PB\Bundle\SuluStorageBundle\Tests\DependencyInjection\Compiler;

use League\Flysystem\FilesystemNotFoundException;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PB\Bundle\SuluStorageBundle\Exception\AliasNotFoundException;
use PB\Bundle\SuluStorageBundle\DependencyInjection\Compiler\StoragePass;
use PB\Bundle\SuluStorageBundle\Manager\PBStorageManager;
use PB\Bundle\SuluStorageBundle\Resolver\Exception\PathResolverNotDefinedException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class StoragePassTest extends AbstractCompilerPassTestCase
{
    public function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new StoragePass());
    }

    public function testCompile()
    {
        $this->setDefinition('sulu_media.storage', new Definition());
        $this->setDefinition('sulu_media.format_cache', new Definition());
        $this->setParameter('sulu_media.image.formats', []);
        $this->setParameter('sulu_media.format_cache.media_proxy_path', '');

        $this->setDefinition('oneup_flysystem.local_filesystem', new Definition());

        $this->setDefinition('pb_sulu_storage.master', new Definition());
        $this->setParameter('pb_sulu_storage.master', ['filesystem' => 'local', 'type' => 'local']);
        $this->setParameter('pb_sulu_storage.replica', ['filesystem' => 'local', 'type' => 'local']);
        $this->setParameter('pb_sulu_storage.format_cache', ['filesystem' => 'local', 'type' => 'local']);

        $pathResolverDef = new Definition();
        $pathResolverDef->addTag('pb_sulu_storage.path_resolver', ['alias' => 'local']);
        $this->setDefinition('pb_sulu_storage.local.path_resolver', $pathResolverDef);

        $this->compile();

        // Storage manager
        $this->assertContainerBuilderHasService('pb_sulu_storage.master.storage_manager', PBStorageManager::class);
        $this->assertContainerBuilderHasService('pb_sulu_storage.replica.storage_manager', PBStorageManager::class);
        $this->assertContainerBuilderHasService('pb_sulu_storage.format_cache.storage_manager', PBStorageManager::class);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'pb_sulu_storage.master.storage_manager',
            0,
            new Reference('oneup_flysystem.local_filesystem')
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'pb_sulu_storage.master.storage_manager',
            1,
            new Reference('pb_sulu_storage.local.path_resolver')
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'pb_sulu_storage.replica.storage_manager',
            0,
            new Reference('oneup_flysystem.local_filesystem')
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'pb_sulu_storage.replica.storage_manager',
            1,
            new Reference('pb_sulu_storage.local.path_resolver')
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'pb_sulu_storage.format_cache.storage_manager',
            0,
            new Reference('oneup_flysystem.local_filesystem')
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'pb_sulu_storage.format_cache.storage_manager',
            1,
            new Reference('pb_sulu_storage.local.path_resolver')
        );

        // Format cache
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'sulu_media.format_cache',
            0,
            $this->container->findDefinition('pb_sulu_storage.format_cache.storage_manager')
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'sulu_media.format_cache',
            1,
            []
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'sulu_media.format_cache',
            2,
            ''
        );
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'sulu_media.format_cache',
            'sulu_media.format_cache',
            ['alias' => 'pb']
        );

        // Storage
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'sulu_media.storage',
            0,
            $this->container->findDefinition('pb_sulu_storage.master.storage_manager')
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'sulu_media.storage',
            1,
            $this->container->findDefinition('pb_sulu_storage.replica.storage_manager')
        );
    }

    public function testCompileWithoutReplicaStorage()
    {
        $this->setDefinition('sulu_media.storage', new Definition());
        $this->setDefinition('sulu_media.format_cache', new Definition());
        $this->setParameter('sulu_media.image.formats', []);
        $this->setParameter('sulu_media.format_cache.media_proxy_path', []);

        $this->setDefinition('oneup_flysystem.local_filesystem', new Definition());

        $this->setDefinition('pb_sulu_storage.master', new Definition());
        $this->setParameter('pb_sulu_storage.master', ['filesystem' => 'local', 'type' => 'local']);
        $this->setParameter('pb_sulu_storage.format_cache', ['filesystem' => 'local', 'type' => 'local']);

        $pathResolverDef = new Definition();
        $pathResolverDef->addTag('pb_sulu_storage.path_resolver', ['alias' => 'local']);
        $this->setDefinition('pb_sulu_storage.local.path_resolver', $pathResolverDef);

        $this->compile();

        // Storage
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('sulu_media.storage',1, null);
    }

    public function testCompileWithExternalUrlResolver()
    {
        $this->setDefinition('sulu_media.storage', new Definition());
        $this->setDefinition('sulu_media.format_cache', new Definition());
        $this->setParameter('sulu_media.image.formats', []);
        $this->setParameter('sulu_media.format_cache.media_proxy_path', []);

        $this->setDefinition('oneup_flysystem.local_filesystem', new Definition());

        $this->setDefinition('pb_sulu_storage.master', new Definition());
        $this->setParameter('pb_sulu_storage.master', ['filesystem' => 'local', 'type' => 'local']);
        $this->setParameter('pb_sulu_storage.format_cache', ['filesystem' => 'local', 'type' => 'local']);

        $pathResolverDef = new Definition();
        $pathResolverDef->addTag('pb_sulu_storage.path_resolver', ['alias' => 'local']);
        $this->setDefinition('pb_sulu_storage.local.path_resolver', $pathResolverDef);

        $extUrlResolverDef = new Definition();
        $extUrlResolverDef->addTag('pb_sulu_storage.external_url_resolver', ['alias' => 'local']);
        $this->setDefinition('pb_sulu_storage.local.external_url_resolver', $extUrlResolverDef);

        $this->compile();

        // Storage Manager
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'pb_sulu_storage.master.storage_manager',
            2,
            new Reference('pb_sulu_storage.local.external_url_resolver')
        );
    }

    public function testCompileWithExternalUrlResolverWithoutAlias()
    {
        $this->setDefinition('sulu_media.storage', new Definition());
        $this->setDefinition('sulu_media.format_cache', new Definition());
        $this->setParameter('sulu_media.image.formats', []);
        $this->setParameter('sulu_media.format_cache.media_proxy_path', []);

        $this->setDefinition('oneup_flysystem.local_filesystem', new Definition());

        $this->setDefinition('pb_sulu_storage.master', new Definition());
        $this->setParameter('pb_sulu_storage.master', ['filesystem' => 'local', 'type' => 'local']);
        $this->setParameter('pb_sulu_storage.format_cache', ['filesystem' => 'local', 'type' => 'local']);

        $resolverDef = new Definition();
        $resolverDef->addTag('pb_sulu_storage.external_url_resolver');
        $this->setDefinition('pb_sulu_storage.local.external_url_resolver', $resolverDef);

        $this->expectException(AliasNotFoundException::class);
        $this->compile();
    }

    public function testCompileWithNotExistOneupFilesystem()
    {
        $this->setDefinition('sulu_media.storage', new Definition());
        $this->setDefinition('sulu_media.format_cache', new Definition());
        $this->setParameter('sulu_media.image.formats', []);
        $this->setParameter('sulu_media.format_cache.media_proxy_path', []);

        $this->setDefinition('pb_sulu_storage.master', new Definition());
        $this->setParameter('pb_sulu_storage.master', ['filesystem' => 'local', 'type' => 'local']);
        $this->setParameter('pb_sulu_storage.format_cache', ['filesystem' => 'local', 'type' => 'local']);

        $this->expectException(FilesystemNotFoundException::class);
        $this->compile();
    }

    public function testCompileWithNotExistPathResolver()
    {
        $this->setDefinition('sulu_media.storage', new Definition());
        $this->setDefinition('sulu_media.format_cache', new Definition());
        $this->setParameter('sulu_media.image.formats', []);
        $this->setParameter('sulu_media.format_cache.media_proxy_path', []);

        $this->setDefinition('oneup_flysystem.local_filesystem', new Definition());

        $this->setDefinition('pb_sulu_storage.master', new Definition());
        $this->setParameter('pb_sulu_storage.master', ['filesystem' => 'local', 'type' => 'local']);
        $this->setParameter('pb_sulu_storage.format_cache', ['filesystem' => 'local', 'type' => 'local']);

        $this->expectException(PathResolverNotDefinedException::class);
        $this->compile();
    }
}