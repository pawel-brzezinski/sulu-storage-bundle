<?php

namespace PB\Bundle\SuluStorageBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;
use PB\Bundle\SuluStorageBundle\DependencyInjection\Configuration;
use PB\Bundle\SuluStorageBundle\DependencyInjection\PBSuluStorageExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ConfigurationTest extends AbstractExtensionConfigurationTestCase
{
    public function testFullConfig()
    {
        $expected = [
            'master' => [
                'type' => 'local',
                'filesystem' => 'local_filesystem',
                'segments' => 10
            ],
            'replica' => [
                'type' => 'local',
                'filesystem' => 'local_filesystem',
            ],
            'format_cache' => [
                'type' => 'local',
                'filesystem' => 'local_filesystem',
            ],
        ];

        $sources = [__DIR__ . '/Fixtures/config-full.yml'];

        $this->assertProcessedConfigurationEquals($expected, $sources);
    }

    public function testConfigWithoutReplica()
    {
        $expected = [
            'master' => [
                'type' => 'local',
                'filesystem' => 'local_filesystem',
                'segments' => 10
            ],
            'format_cache' => [
                'type' => 'local',
                'filesystem' => 'local_filesystem',
            ],
        ];

        $sources = [__DIR__ . '/Fixtures/config-no-replica.yml'];

        $this->assertProcessedConfigurationEquals($expected, $sources);
    }

    public function testConfigWithoutMaster()
    {
        $sources = [__DIR__ . '/Fixtures/config-no-master.yml'];

        $this->expectException(InvalidConfigurationException::class);
        $this->assertProcessedConfigurationEquals([], $sources);
    }

    public function testConfigWithoutFormatCache()
    {
        $sources = [__DIR__ . '/Fixtures/config-no-format-cache.yml'];

        $this->expectException(InvalidConfigurationException::class);
        $this->assertProcessedConfigurationEquals([], $sources);
    }

    public function testConfigWithoutMasterFilesystem()
    {
        $sources = [__DIR__ . '/Fixtures/config-no-master-filesystem.yml'];

        $this->expectException(InvalidConfigurationException::class);
        $this->assertProcessedConfigurationEquals([], $sources);
    }

    public function testConfigWithoutMasterFilesystemType()
    {
        $sources = [__DIR__ . '/Fixtures/config-no-master-filesystem-type.yml'];

        $this->expectException(InvalidConfigurationException::class);
        $this->assertProcessedConfigurationEquals([], $sources);
    }

    public function testConfigWithoutReplicaFilesystem()
    {
        $sources = [__DIR__ . '/Fixtures/config-no-replica-filesystem.yml'];

        $this->expectException(InvalidConfigurationException::class);
        $this->assertProcessedConfigurationEquals([], $sources);
    }

    /**
     * {@inheritdoc}
     *
     * @return ExtensionInterface
     */
    protected function getContainerExtension()
    {
        return new PBSuluStorageExtension();
    }

    /**
     * {@inheritdoc}
     *
     * @return ConfigurationInterface
     */
    protected function getConfiguration()
    {
        return new Configuration();
    }
}