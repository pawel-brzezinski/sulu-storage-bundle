<?php

namespace PB\Bundle\SuluStorageBundle\HttpFoundation\Exception;

use League\Flysystem\File;

/**
 * Thrown when file variable passed to BinaryFlysystemFileManagerResponse is not an  Flysystem File Manager instance.
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
class BadFileManagerInstanceException extends \Exception
{
    /**
     * FileNotFoundException constructor.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, \Throwable $previous = null)
    {
        $message = 'BinaryFlysystemFileManagerResponse expects instance of type %s.';

        parent::__construct(sprintf($message, File::class), $code, $previous);
    }
}
