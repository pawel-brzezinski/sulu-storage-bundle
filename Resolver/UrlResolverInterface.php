<?php

namespace PB\Bundle\SuluStorageBundle\Resolver;

use League\Flysystem\AdapterInterface;

/**
 * Interface for Flysystem adapters url resolvers
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
interface UrlResolverInterface
{
    /**
     * Get url to file.
     *
     * @param AdapterInterface $adapter
     * @param string $fileName
     *
     * @return string|null
     */
    public function getUrl(AdapterInterface $adapter, $fileName);
}
