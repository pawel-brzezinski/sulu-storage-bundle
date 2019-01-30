<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Flysystem\Exception;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Adapter\NullAdapter;
use PB\Bundle\SuluStorageBundle\Flysystem\Exception\InvalidAdapterException;
use PHPUnit\Framework\TestCase;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class InvalidAdapterExceptionTest extends TestCase
{
    public function testException()
    {
        // Expect
        $current = get_class(new NullAdapter());
        $required = get_class(new Local('/'));
        $exceptionMsg = sprintf('Flysystem adapter "%s" is not an instance of "%s"', $current, $required);

        $this->expectException(InvalidAdapterException::class);
        $this->expectExceptionCode(InvalidAdapterException::INVALID_ADAPTER_CODE);
        $this->expectExceptionMessage($exceptionMsg);

        // When
        throw new InvalidAdapterException($current, $required);
    }
}
