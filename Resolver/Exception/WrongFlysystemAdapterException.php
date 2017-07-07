<?php

namespace PB\Bundle\SuluStorageBundle\Resolver\Exception;

use LogicException;

/**
 * Thrown when wrong Flysystem adapter has been used.
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
class WrongFlysystemAdapterException extends LogicException
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
        parent::__construct(sprintf('Wrong Flysystem adapter has been passed. Required adapter: \'%s\'.', $message), $code, $previous);
    }
}
