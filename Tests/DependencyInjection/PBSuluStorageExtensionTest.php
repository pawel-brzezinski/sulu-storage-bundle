<?php

namespace PB\Bundle\SuluStorageBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use PB\Bundle\SuluStorageBundle\DependencyInjection\PBSuluStorageExtension;
use PB\Bundle\SuluStorageBundle\Flysystem\Plugin\ContentPath\LocalContentPathPlugin;
use PB\Component\Overlay\File\FileOverlay;
use PB\Component\Overlay\Math\MathOverlay;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class PBSuluStorageExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return [
            new PBSuluStorageExtension(),
        ];
    }

    public function definedServiceDataProvider()
    {
        return [
            'content path plugin for local adapter' => ['pb_sulu_storage.flysystem.local.content_path.plugin', LocalContentPathPlugin::class],
            'file overlay' => ['pb_sulu_storage.file.overlay', FileOverlay::class],
            'math overlay' => ['pb_sulu_storage.math.overlay', MathOverlay::class],
        ];
    }

    /**
     * @dataProvider definedServiceDataProvider
     *
     * @param string $expectedServiceId
     * @param string $expectedClass
     */
    public function testShouldCheckIfContainerBuilderHasDefinedService($expectedServiceId, $expectedClass)
    {
        // When
        $this->load([
            'provider' => 'flysystem',
            'flysystem' => [
                'filesystem' => ['storage' => 'test_storage', 'format_cache' => 'test_storage']
            ],
        ]);

        // Then
        $this->assertContainerBuilderHasService($expectedServiceId, $expectedClass);
    }

    public function definedParameterDataProvider()
    {
        $config = [
            'provider' => 'flysystem',
            'flysystem' => [
                'filesystem' => ['storage' => 'test_storage', 'format_cache' => 'test_storage']
            ],
            'segments' => 15,
            'logger' => 'logger',
        ];

        return [
            'pb_sulu_storage.config' => ['pb_sulu_storage.config', $config, $config],
        ];
    }

    /**
     * @dataProvider definedParameterDataProvider
     *
     * @param string $expectedParamId
     * @param string $expectedParamValue
     * @param array $config
     */
    public function testShouldCheckIfContainerBuilderHasDefinedParameter($expectedParamId, $expectedParamValue, array $config)
    {
        // When
        $this->load($config);

        // Then
        $this->assertContainerBuilderHasParameter($expectedParamId, $expectedParamValue);
    }
}
