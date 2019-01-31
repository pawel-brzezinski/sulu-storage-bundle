<?php

namespace PB\Bundle\SuluStorageBundle\Flysystem\Plugin\ContentPath;

use League\Flysystem\AwsS3v3\AwsS3Adapter;
use PB\Bundle\SuluStorageBundle\Flysystem\Plugin\AbstractContentPathPlugin;

/**
 * Flysystem path to file content plugin for AWS S3v3 adapter.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class AwsS3v3ContentPathPlugin extends AbstractContentPathPlugin
{
    /**
     * @var string
     */
    protected $supportedAdapter = AwsS3Adapter::class;

    /**
     * @var AwsS3Adapter
     */
    protected $adapter;

    /**
     * Handle plugin.
     *
     * @param $path
     *
     * @return string
     */
    public function handle($path)
    {
        $path = $this->adapter->applyPathPrefix($path);
        $bucket = $this->adapter->getBucket();

        return $this->adapter->getClient()->getObjectUrl($bucket, $path);
    }
}
