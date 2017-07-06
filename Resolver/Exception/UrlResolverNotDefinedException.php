<?php

namespace PB\Bundle\SuluStorageBundle\Resolver\Exception;

use LogicException;

/**
 * Thrown when requested url resolver is not defined.
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
class UrlResolverNotDefinedException extends LogicException
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
        parent::__construct(sprintf('Url resolver for filesystem \'%s\' is not defined.', $message), $code, $previous);
    }
}