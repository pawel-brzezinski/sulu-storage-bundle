<?php

namespace PB\Bundle\SuluStorageBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use PB\Bundle\SuluStorageBundle\DependencyInjection\PBSuluStorageExtension;

class PBSuluStorageExtensionTest extends AbstractExtensionTestCase
{
    public function testSetParametersAfterLoading()
    {
        $config = [
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

        $this->load($config);

        $this->assertContainerBuilderHasParameter('pb_sulu_storage.master');
        $this->assertContainerBuilderHasParameter('pb_sulu_storage.replica');
        $this->assertContainerBuilderHasParameter('pb_sulu_storage.format_cache');
    }


    /**
     * @inheritdoc
     *
     * @return array
     */
    protected function getContainerExtensions()
    {
        return [
            new PBSuluStorageExtension(),
        ];
    }
}