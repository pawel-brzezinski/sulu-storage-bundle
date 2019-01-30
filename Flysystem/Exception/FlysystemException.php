<?php

namespace PB\Bundle\SuluStorageBundle\Flysystem\Exception;

/**
 * Flysystem common exception.
 *
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class FlysystemException extends \Exception
{
    const INVALID_FILESYSTEM_CODE = 1001;
    const INVALID_ADAPTER_CODE = 1002;
    const FILESYSTEM_NOT_FOUND_CODE = 1003;
}
