<?php

namespace PB\Bundle\SuluStorageBundle\Exception;

use LogicException;

/**
 * Thrown when the tagged service don't have required alias value.
 *
 * @author Pawel Brzezinski <pawel.brzezinski@smartint.pl>
 */
class AliasNotFoundException extends LogicException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Tag \'%s\' require an alias attribute.', $message), $code, $previous);
    }
}