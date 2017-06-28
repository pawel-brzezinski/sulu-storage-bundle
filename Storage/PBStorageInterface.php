<?php

namespace PB\Bundle\SuluStorageBundle\Storage;

/**
 * Interface for PB storage
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
interface PBStorageInterface
{
    /**
     * Get media external url.
     *
     * @param string $fileName
     * @param string|null $storageOption    JSON string
     *
     * @return string|null
     */
    public function getMediaUrl($fileName, $storageOption = null);

    /**
     * Load media stream.
     *
     * @param string $fileName
     * @param string|null $storageOption    JSON string
     *
     * @return null|false|resource
     */
    public function loadStream($fileName, $storageOption = null);
}