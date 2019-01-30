<?php

namespace PB\Bundle\SuluStorageBundle\Media\Storage;

use PB\Bundle\SuluStorageBundle\Config\AbstractConfig;

/**
 * Storage config.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
final class StorageConfig extends AbstractConfig implements StorageConfigInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSegment()
    {
        return $this->get(self::SEGMENT_OPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setSegment($value)
    {
        $this->set(self::SEGMENT_OPTION, (int) $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileName()
    {
        return $this->get(self::FILENAME_OPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setFileName($value)
    {
        $this->set(self::FILENAME_OPTION, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentPath()
    {
        return $this->get(self::CONTENT_PATH_OPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setContentPath($value)
    {
        $this->set(self::CONTENT_PATH_OPTION, $value);
        return $this;
    }
}
