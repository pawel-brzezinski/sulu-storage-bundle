<?php

namespace PB\Bundle\SuluStorageBundle\Flysystem\Plugin\ContentPath;

use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Cached\CachedAdapter;
use PB\Bundle\SuluStorageBundle\Flysystem\Plugin\AbstractContentPathPlugin;

/**
 * Flysystem path to file content plugin for local adapter.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class LocalContentPathPlugin extends AbstractContentPathPlugin
{
    /**
     * @var string
     */
    protected $supportedAdapter = LocalAdapter::class;

    /**
     * @var LocalAdapter
     */
    protected $adapter;

    /**
     * Handle plugin.
     *
     * @param $path
     * @param null $host
     *
     * @return string
     */
    public function handle($path, $host = null)
    {
        $path = $this->adapter->applyPathPrefix($path);

        if (null !== $host) {
            $path = rtrim($host, '/').'/'.ltrim($path, '/');
        }

        return $path;
    }
}
