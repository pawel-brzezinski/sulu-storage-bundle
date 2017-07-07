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
        $adapter = $this->extractAdapter($adapter);

        if (!$adapter instanceof AbstractAdapter) {
            return null;
        }

        return $adapter->applyPathPrefix($fileName);
    }

    /**
     * Extract adapter if it is cached adapter.
     *
     * @param AdapterInterface $adapter
     *
     * @return AdapterInterface
     */
    protected function extractAdapter(AdapterInterface $adapter)
    {
        return $adapter instanceof CachedAdapter ? $adapter->getAdapter() : $adapter;
    }
}