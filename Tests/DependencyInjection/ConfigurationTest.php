<?php

namespace PB\Bundle\SuluStorageBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PB\Bundle\SuluStorageBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration();
    }

    public function invalidConfigurationDataProvider()
    {
        $configNoSegments = [
            'provider' => 'flysystem',
            'flysystem' => [
                'filesystem' => [
                    'storage' => 'storage_fs',
                ],
            ],
            'segments' => '',
        ];

        return [
            'no configuration values' => [[]],
            'provider not supported' => [['provider' => 'foo']],
            'segments value is empty' => [$configNoSegments],
        ];
    }

    /**
     * @dataProvider invalidConfigurationDataProvider
     *
     * @param array $configuration
     */
    public function testConfigurationIsInvalid(array $configuration)
    {
        // Then
        $this->assertConfigurationIsInvalid([$configuration]);
    }

    public function invalidFlysystemConfigurationDataProvider()
    {
        $config1 = ['provider' => 'flysystem'];
        $config2 = [
            'provider' => 'flysystem',
            'flysystem' => [],
        ];
        $config3 = [
            'provider' => 'flysystem',
            'flysystem' => [
                'filesystem' => [
                    'format_cache' => 'storage_fs',
                ],
            ],
        ];
        $config4 = [
            'provider' => 'flysystem',
            'flysystem' => [
                'filesystem' => [
                    'storage' => 'storage_fs',
                ],
            ],
        ];

        return [
            'flysystem provider without flysystem configuration' => [$config1],
            'flysystem provider where flysystem filesystem is not defined' => [$config2],
            'flysystem provider where flysystem filesystem storage is not defined' => [$config3],
            'flysystem provider where flysystem filesystem format cache is not defined' => [$config4],
        ];
    }

    /**
     * @dataProvider invalidFlysystemConfigurationDataProvider
     *
     * @param array $configuration
     */
    public function testConfigurationIsInvalidForFlysystemProvider(array $configuration)
    {
        // Then
        $this->assertConfigurationIsInvalid([$configuration]);
    }

    public function validConfigurationDataProvider()
    {
        $config1 = [
            'provider' => 'flysystem',
            'flysystem' => [
                'filesystem' => [
                    'storage' => 'storage_fs',
                    'format_cache' => 'storage_fs',
                ],
            ],
            'segments' => 11,
            'logger' => 'custom_logger',
        ];

        $config2 = [
            'provider' => 'flysystem',
            'flysystem' => [
                'filesystem' => [
                    'storage' => 'storage_fs',
                    'format_cache' => 'storage_fs',
                ],
            ],
        ];

        return [
            'configuration for Flysystem provider with custom segments and logger option' => [$config1],
            'configuration for Flysystem provider with default segments and logger option' => [$config2],
        ];
    }

    /**
     * @dataProvider validConfigurationDataProvider
     *
     * @param array $configuration
     */
    public function testConfigurationIsValid(array $configuration)
    {
        // Then
        $this->assertConfigurationIsValid([$configuration]);
    }

    public function processedConfigurationDataProvider()
    {
        $config1 = [
            'provider' => 'flysystem',
            'flysystem' => [
                'filesystem' => [
                    'storage' => 'storage_fs',
                    'format_cache' => 'storage_fs',
                ],
            ],
        ];
        $expected1 = [
            'provider' => 'flysystem',
            'flysystem' => [
                'filesystem' => [
                    'storage' => 'storage_fs',
                    'format_cache' => 'storage_fs',
                ],
            ],
            'segments' => 10,
            'logger' => 'logger',
        ];

        return [
            'default segment and logger configuration' => [$expected1, $config1],
        ];
    }

    /**
     * @dataProvider processedConfigurationDataProvider
     *
     * @param array $expected
     * @param array $config
     */
    public function testProcessedConfiguration(array $expected, array $config)
    {
        // Then
        $this->assertProcessedConfigurationEquals([$config], $expected);
    }
}
