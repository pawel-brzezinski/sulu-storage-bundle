<?php

namespace PB\Bundle\SuluStorageBundle\Resolver;

use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

/**
 * Flysystem S3v3 adapter path resolver
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
class AwsS3v3PathResolver extends AbstractPathResolver
{
    /**
     * {@inheritdoc}
     *
     * @param AdapterInterface $adapter
     * @param string $fileName
     *
     * @return null|string
     */
    public function getRelativePath(AdapterInterface $adapter, $fileName)
    {
        return $this->getFullPath($adapter, $fileName);
    }
}