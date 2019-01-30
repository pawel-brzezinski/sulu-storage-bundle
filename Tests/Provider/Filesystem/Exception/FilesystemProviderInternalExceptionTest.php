<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Provider\Filesystem\Exception;

use PB\Bundle\SuluStorageBundle\Provider\Filesystem\Exception\FilesystemProviderInternalException;
use PHPUnit\Framework\TestCase;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class FilesystemProviderInternalExceptionTest extends TestCase
{
    public function exceptionDataProvider()
    {
        return [
            'default code parameter' => [0, null],
            'custom code parameter' => [123, 123],
        ];
    }

    /**
     * @dataProvider exceptionDataProvider
     *
     * @param $expectedCode
     * @param $code
     *
     * @throws FilesystemProviderInternalException
     */
    public function testException($expectedCode, $code)
    {
        // Expect
        $msg = 'Test exception message';
        $exceptionMsg = sprintf('Filesystem provider error occurred (%d): %s', $expectedCode, $msg);

        $this->expectException(FilesystemProviderInternalException::class);
        $this->expectExceptionCode(FilesystemProviderInternalException::INTERNAL_EXCEPTION_CODE);
        $this->expectExceptionMessage($exceptionMsg);

        // When
        if (null === $code) {
            throw new FilesystemProviderInternalException($msg);
        } else {
            throw new FilesystemProviderInternalException($msg, $code);
        }
    }
}
