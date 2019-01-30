<?php

namespace PB\Bundle\SuluStorageBundle\Flysystem\Exception;

/**
 * Exception which is thrown when Flysystem filesystem service not found.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class FilesystemNotFoundException extends FlysystemException
{
    /**
     * FlysystemFilesystemNotFoundException constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $message = sprintf('Flysystem filesystem "%s" is not defined', $name);

        parent::__construct($message, self::FILESYSTEM_NOT_FOUND_CODE);
    }
}
