<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Flysystem\Exception;

use PB\Bundle\SuluStorageBundle\Flysystem\Exception\FilesystemNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class FilesystemNotFoundExceptionTest extends TestCase
{
    public function testException()
    {
        // Expect
        $name = 'foobar';
        $exceptionMsg = sprintf('Flysystem filesystem "%s" is not defined', $name);

        $this->expectException(FilesystemNotFoundException::class);
        $this->expectExceptionCode(FilesystemNotFoundException::FILESYSTEM_NOT_FOUND_CODE);
        $this->expectExceptionMessage($exceptionMsg);

        // When
        throw new FilesystemNotFoundException($name);
    }
}
