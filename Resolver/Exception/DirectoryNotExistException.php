<?php

namespace PB\Bundle\SuluStorageBundle\Resolver\Exception;

use LogicException;

/**
 * Thrown when path to directory not exist or is not writable.
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
class DirectoryNotExistException extends LogicException
{
    /**
     * UrlResolverNotDefinedException constructor.
     *
     * @param string $message
     * @param int $code
     *
     * @param \Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct(sprintf('The path \'%s\' not exist or is not writable.', $message), $code, $previous);
    }
}
