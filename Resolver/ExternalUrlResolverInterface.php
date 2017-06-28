<?php

namespace PB\Bundle\SuluStorageBundle\Resolver;

use League\Flysystem\AdapterInterface;

/**
 * Interface for Flysystem adapters external url resolvers
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
interface ExternalUrlResolverInterface
{
    /**
     * Get external url to file. Return null if external url is not supported.
     *
     * @param AdapterInterface $adapter
     * @param string $fileName
     *
     * @return string|null
     */
    public function getUrl(AdapterInterface $adapter, $fileName);
}
