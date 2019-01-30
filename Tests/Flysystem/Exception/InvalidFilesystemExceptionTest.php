<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Flysystem\Exception;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use PB\Bundle\SuluStorageBundle\Flysystem\Exception\InvalidFilesystemException;
use PHPUnit\Framework\TestCase;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class InvalidFilesystemExceptionTest extends TestCase
{
    public function testException()
    {
        // Expect
        $fs = $this->prophesize(FilesystemInterface::class)->reveal();
        $exceptionMsg = sprintf('Filesystem "%s" is not an extension of "%s"', get_class($fs), Filesystem::class);

        $this->expectException(InvalidFilesystemException::class);
        $this->expectExceptionCode(InvalidFilesystemException::INVALID_FILESYSTEM_CODE);
        $this->expectExceptionMessage($exceptionMsg);

        // When
        throw new InvalidFilesystemException($fs);
    }
}
