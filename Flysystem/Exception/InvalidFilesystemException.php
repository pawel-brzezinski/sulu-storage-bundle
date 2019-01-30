<?php

namespace PB\Bundle\SuluStorageBundle\Flysystem\Exception;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;

/**
 * Exception is thrown when filesystem is not valid Flysystem filesystem instance.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class InvalidFilesystemException extends FlysystemException
{
    /**
     * InvalidFilesystemException constructor.
     *
     * @param FilesystemInterface $filesystem
     */
    public function __construct(FilesystemInterface $filesystem)
    {
        $message = sprintf('Filesystem "%s" is not an extension of "%s"', get_class($filesystem), Filesystem::class);

        parent::__construct($message, self::INVALID_FILESYSTEM_CODE);
    }
}
