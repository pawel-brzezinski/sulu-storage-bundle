<?php

namespace PB\Bundle\SuluStorageBundle\Flysystem\Plugin\ContentPath;

use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;
use PB\Bundle\SuluStorageBundle\Flysystem\Plugin\AbstractContentPathPlugin;

/**
 * Flysystem path to file content plugin for AWS S3v3 adapter.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class GoogleStorageContentPathPlugin extends AbstractContentPathPlugin
{
    /**
     * @var string
     */
    protected $supportedAdapter = GoogleStorageAdapter::class;

    /**
     * @var GoogleStorageAdapter
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
        return $this->adapter->getUrl($path);
    }
}
