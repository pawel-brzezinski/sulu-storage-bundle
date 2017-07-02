<?php

namespace PB\Bundle\SuluStorageBundle\Resolver\Exception;

use LogicException;

/**
 * Thrown when requested path resolver is not defined.
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
class PathResolverNotDefinedException extends LogicException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Path resolver for filesystem \'%s\' is not defined.', $message), $code, $previous);
    }
}