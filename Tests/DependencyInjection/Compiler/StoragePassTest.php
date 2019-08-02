<?php

namespace PB\Bundle\SuluStorageBundle\Tests\DependencyInjection\Compiler;

use League\Flysystem\Filesystem;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PB\Bundle\SuluStorageBundle\DependencyInjection\Compiler\StoragePass;
use PB\Bundle\SuluStorageBundle\DependencyInjection\PBSuluStorageExtension;
use PB\Bundle\SuluStorageBundle\Flysystem\Exception\FilesystemNotFoundException;
use PB\Bundle\SuluStorageBundle\Media\FormatManager\PBFormatManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class StoragePassTest extends AbstractCompilerPassTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new StoragePass());
    }

    public function testShouldCheckIfCompilerPassOverloadSuluMediaServices()
    {
        // Given
        $configParamId = PBSuluStorageExtension::BUNDLE_CONFIG_PARAM;
        $config = [
            'provider' => 'flysystem',
            'flysystem' => [
                'filesystem' => ['storage' => 'test_storage', 'format_cache' => 'test_format_cache']
            ],
            'segments' => 10,
            'logger' => 'logger',
        ];
        $this->setParameter($configParamId, $config);

        // Oneup Flysystem filesystem service def
        $fsStorageDef = new Definition();
        $fsStorageDef->setClass(Filesystem::class);
        $this->setDefinition('oneup_flysystem.test_storage_filesystem', $fsStorageDef);

        $fsFormatCacheDef = new Definition();
        $fsFormatCacheDef->setClass(Filesystem::class);
        $this->setDefinition('oneup_flysystem.test_format_cache_filesystem', $fsFormatCacheDef);
        // End

        // Base Sulu Media storage def
        $suluStorageDef = new Definition();
        $this->setDefinition(StoragePass::SULU_MEDIA_STORAGE_SERVICE_ID, $suluStorageDef);
        // End

        // Base Sulu Media format cache def
        $suluFormatCacheDef = new Definition();
        $this->setDefinition(StoragePass::SULU_MEDIA_FORMAT_CACHE_SERVICE_ID, $suluFormatCacheDef);
        // End

        // Base Sulu Media format manager def
        $suluFormatManagerDef = new Definition();
        $this->setDefinition(StoragePass::SULU_MEDIA_FORMAT_MANAGER_SERVICE_ID, $suluFormatManagerDef);
        // End

        // Necessary standard Sulu parameters
        $this->setParameter('sulu_media.format_cache.media_proxy_path', '/proxy/path');
        $this->setParameter('sulu_media.image.formats', ['sulu-400x400' => [], 'sulu-260x' => []]);
        // End

        // When
        $this->compile();

        // Then
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::STORAGE_FILESYSTEM_PROVIDER_SERVICE_ID, 0);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::STORAGE_FILE_RESOLVER_SERVICE_ID, 0);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::STORAGE_FILE_RESOLVER_SERVICE_ID, 1);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::STORAGE_FILE_RESOLVER_SERVICE_ID, 2);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::FORMAT_CACHE_FILESYSTEM_PROVIDER_SERVICE_ID, 0);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::FORMAT_CACHE_FILE_RESOLVER_SERVICE_ID, 0);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::FORMAT_CACHE_FILE_RESOLVER_SERVICE_ID, 1);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::FORMAT_CACHE_FILE_RESOLVER_SERVICE_ID, 2);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::SULU_MEDIA_STORAGE_SERVICE_ID, 0);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::SULU_MEDIA_STORAGE_SERVICE_ID, 1);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::SULU_MEDIA_STORAGE_SERVICE_ID, 2);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::SULU_MEDIA_STORAGE_SERVICE_ID, 3);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::SULU_MEDIA_STORAGE_SERVICE_ID, 4, 10);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::SULU_MEDIA_STORAGE_SERVICE_ID, 5);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::SULU_MEDIA_FORMAT_CACHE_SERVICE_ID, 0);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::SULU_MEDIA_FORMAT_CACHE_SERVICE_ID, 1);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::SULU_MEDIA_FORMAT_CACHE_SERVICE_ID, 2);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::SULU_MEDIA_FORMAT_CACHE_SERVICE_ID, 3, 10);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(StoragePass::SULU_MEDIA_FORMAT_CACHE_SERVICE_ID, 4);

        $this->assertContainerBuilderHasService(StoragePass::SULU_MEDIA_FORMAT_MANAGER_SERVICE_ID, PBFormatManager::class);
    }

    public function testShouldThrowFilesystemNotFoundExceptionWhenDefinedFlysystemFilesystemNotExist()
    {
        // Expect
        $this->expectException(FilesystemNotFoundException::class);
        $this->expectExceptionCode(FilesystemNotFoundException::FILESYSTEM_NOT_FOUND_CODE);
        $this->expectExceptionMessage(sprintf('Flysystem filesystem "%s" is not defined', 'oneup_flysystem.test_storage_filesystem'));

        // Given
        $configParamId = PBSuluStorageExtension::BUNDLE_CONFIG_PARAM;
        $config = [
            'provider' => 'flysystem',
            'flysystem' => [
                'filesystem' => ['storage' => 'test_storage']
            ],
            'segments' => 10,
            'logger' => 'logger',
        ];
        $this->setParameter($configParamId, $config);

        // When
        $this->compile();
    }
}
