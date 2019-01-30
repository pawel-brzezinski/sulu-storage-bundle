<?php

namespace PB\Bundle\SuluStorageBundle\Media\FormatCache;

use PB\Bundle\SuluStorageBundle\Provider\Filesystem\FilesystemProviderInterface;
use Sulu\Bundle\MediaBundle\Media\FormatCache\FormatCacheInterface as BaseFormatCacheInterface;

/**
 * Interface for PB Sulu format cache implementation.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
interface FormatCacheInterface extends BaseFormatCacheInterface
{
    /**
     * Get filesystem provider.
     *
     * @return FilesystemProviderInterface
     */
    public function getFilesystemProvider();

    public function load($id, $fileName, $options, $format);
}
