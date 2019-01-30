<?php

namespace PB\Bundle\SuluStorageBundle\Media\Storage;

use PB\Bundle\SuluStorageBundle\Config\ConfigInterface;

/**
 * Interface for storage config implementation.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
interface StorageConfigInterface extends ConfigInterface
{
    const SEGMENT_OPTION = 'segment';
    const FILENAME_OPTION = 'fileName';
    const CONTENT_PATH_OPTION = 'contentPath';

    /**
     * Get segment option.
     *
     * @return int|null
     */
    public function getSegment();

    /**
     * Set segment option.
     *
     * @param int $value
     *
     * @return StorageConfigInterface
     */
    public function setSegment($value);

    /**
     * Get file name option.
     *
     * @return string|null
     */
    public function getFileName();

    /**
     * Set file name option.
     *
     * @param string $value
     *
     * @return StorageConfigInterface
     */
    public function setFileName($value);

    /**
     * Get content path option.
     *
     * @return string|null
     */
    public function getContentPath();

    /**
     * Set content path option.
     *
     * @param string $value
     *
     * @return StorageConfigInterface
     */
    public function setContentPath($value);
}
