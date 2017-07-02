<?php

namespace PB\Bundle\SuluStorageBundle\Resolver;

use League\Flysystem\AdapterInterface;

/**
 * Interface for Flysystem adapters path resolvers
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
interface PathResolverInterface
{
    /**
     * Get full path to resource.
     *
     * @param AdapterInterface $adapter
     * @param string $fileName
     *
     * @return string
     */
    public function getFullPath(AdapterInterface $adapter, $fileName);
}
