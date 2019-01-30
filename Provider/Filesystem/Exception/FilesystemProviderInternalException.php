<?php

namespace PB\Bundle\SuluStorageBundle\Provider\Filesystem\Exception;

/**
 * Exception which is thrown when any error in any filesystem provider will occur.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class FilesystemProviderInternalException extends FilesystemProviderException
{
    /**
     * FilesystemProviderInternalException constructor.
     *
     * @param string $message
     * @param int $code
     */
    public function __construct($message, $code = 0)
    {
        $message = sprintf('Filesystem provider error occurred (%d): %s', $code, $message);

        parent::__construct($message, self::INTERNAL_EXCEPTION_CODE);
    }
}
