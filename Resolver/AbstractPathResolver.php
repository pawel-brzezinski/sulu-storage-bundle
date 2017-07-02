<?php

namespace PB\Bundle\SuluStorageBundle\Resolver;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Cached\CachedAdapter;

/**
 * Abstract for Flysystem adapter path resolvers.
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 * @abstract
 */
abstract class AbstractPathResolver implements PathResolverInterface
{
    /**
     * {@inheritdoc}
     *
     * @param AdapterInterface $adapter
     * @param string $fileName
     *
     * @return null|string
     */
    public function getFullPath(AdapterInterface $adapter, $fileName)
    {
        if ($adapter instanceof CachedAdapter) {
            return $this->getFullPath($adapter->getAdapter(), $fileName);
        }

        if (!$adapter instanceof AbstractAdapter) {
            return null;
        }

        return $adapter->applyPathPrefix($fileName);
    }
}