<?php

namespace PB\Bundle\SuluStorageBundle\Resolver;

use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Cached\CachedAdapter;

/**
 * Flysystem S3v3 adapter external url resolver
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
class AwsS3v3ExternalUrlResolver implements ExternalUrlResolverInterface
{
    /**
     * {@inheritdoc}
     *
     * @param AdapterInterface $adapter
     * @param string $fileName
     *
     * @return null|string
     */
    public function getUrl(AdapterInterface $adapter, $fileName)
    {
        if ($adapter instanceof CachedAdapter) {
            $adapter = $adapter->getAdapter();
        }
 
        if (!$adapter instanceof AwsS3Adapter) {
            return null;
        }

        $bucket = $adapter->getBucket();
        $key = $adapter->applyPathPrefix($fileName);

        return $adapter->getClient()->getObjectUrl($bucket, $key);
    }
}
