<?php

namespace PB\Bundle\SuluStorageBundle\Resolver;

use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use PB\Bundle\SuluStorageBundle\Resolver\Exception\WrongFlysystemAdapterException;

/**
 * Flysystem S3v3 adapter url resolver
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
class AwsS3v3UrlResolver implements UrlResolverInterface
{
    /**
     * {@inheritdoc}
     *
     * @param AdapterInterface $adapter
     * @param string $fileName
     *
     * @return string
     *
     * @throws WrongFlysystemAdapterException
     */
    public function getUrl(AdapterInterface $adapter, $fileName)
    {
        if (!$adapter instanceof AwsS3Adapter) {
            throw new WrongFlysystemAdapterException(AwsS3Adapter::class);
        }

        $bucket = $adapter->getBucket();
        $key = $adapter->applyPathPrefix($fileName);

        return $adapter->getClient()->getObjectUrl($bucket, $key);
    }
}