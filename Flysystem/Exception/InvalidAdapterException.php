<?php

namespace PB\Bundle\SuluStorageBundle\Flysystem\Exception;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;

/**
 * Exception is thrown when Flysystem adapter is not instance of supported adapter.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class InvalidAdapterException extends FlysystemException
{
    /**
     * InvalidAdapterException constructor.
     *
     * @param string $current
     * @param string $required
     */
    public function __construct($current, $required)
    {
        $message = sprintf('Flysystem adapter "%s" is not an instance of "%s"', $current, $required);

        parent::__construct($message, self::INVALID_ADAPTER_CODE);
    }
}
