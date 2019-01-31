<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Media\Storage;

use PB\Bundle\SuluStorageBundle\Media\Resolver\FileResolverInterface;
use PB\Bundle\SuluStorageBundle\Media\Storage\PBStorage;
use PB\Bundle\SuluStorageBundle\Media\Storage\StorageConfig;
use PB\Bundle\SuluStorageBundle\Provider\Filesystem\Exception\FilesystemProviderInternalException;
use PB\Bundle\SuluStorageBundle\Provider\Filesystem\FilesystemProviderInterface;
use PB\Bundle\SuluStorageBundle\Tests\Library\Utils\Reflection;
use PB\Component\Overlay\File\FileOverlay;
use PB\Component\Overlay\Math\MathOverlay;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sulu\Bundle\MediaBundle\Media\Exception\ImageProxyMediaNotFoundException;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class PBStorageTest extends TestCase
{
    const DEFAULT_SEGMENTS = 15;

    /** @var ObjectProphecy|FilesystemProviderInterface */
    private $fspMock;

    /** @var ObjectProphecy|FileResolverInterface */
    private $frMock;

    /** @var ObjectProphecy|FileOverlay */
    private $fOverlayMock;

    /** @var ObjectProphecy|MathOverlay */
    private $mOverlayMock;

    /**
     * @var ObjectProphecy|LoggerInterface
     */
    private $loggerMock;

    protected function setUp()
    {
        $this->fspMock = $this->prophesize(FilesystemProviderInterface::class);
        $this->frMock = $this->prophesize(FileResolverInterface::class);
        $this->fOverlayMock = $this->prophesize(FileOverlay::class);
        $this->mOverlayMock = $this->prophesize(MathOverlay::class);
        $this->loggerMock = $this->prophesize(LoggerInterface::class);
    }

    protected function tearDown()
    {
        $this->fspMock = null;
        $this->frMock = null;
        $this->fOverlayMock = null;
        $this->mOverlayMock = null;
        $this->loggerMock = null;
    }

    public function constructDataProvider()
    {
        $defLogger = new NullLogger();
        $customLogger = $this->prophesize(LoggerInterface::class)->reveal();

        return [
            'default logger parameter' => [5, $defLogger, 5, null],
            'custom logger parameter' => [5, $customLogger, 5, $customLogger],
            'segments parameter as string' => [5, $customLogger, '5', $customLogger],
        ];
    }

    /**
     * @dataProvider constructDataProvider
     *
     * @param $expectedSegments
     * @param $expectedLogger
     * @param $segments
     * @param null|LoggerInterface $logger
     *
     * @throws \ReflectionException
     */
    public function testConstruct($expectedSegments, $expectedLogger, $segments, $logger = null)
    {
        // Given
        $fsp = $this->fspMock->reveal();
        $fr = $this->frMock->reveal();
        $fo = $this->fOverlayMock->reveal();
        $mo = $this->mOverlayMock->reveal();

        if (null === $logger) {
            $storageUnderTest = new PBStorage($fsp, $fr, $fo, $mo, $segments);
        } else {
            $storageUnderTest = new PBStorage($fsp, $fr, $fo, $mo, $segments, $logger);
        }

        // When
        $actualFsp = Reflection::getPropertyValue($storageUnderTest, 'filesystemProvider');
        $actualFr = Reflection::getPropertyValue($storageUnderTest, 'fileResolver');
        $actualFo = Reflection::getPropertyValue($storageUnderTest, 'fileOverlay');
        $actualMo = Reflection::getPropertyValue($storageUnderTest, 'mathOverlay');
        $actualSegments = Reflection::getPropertyValue($storageUnderTest, 'segments');
        $actualLogger = Reflection::getPropertyValue($storageUnderTest, 'logger');

        // Then
        $this->assertSame($fsp, $actualFsp);
        $this->assertSame($fr, $actualFr);
        $this->assertSame($fo, $actualFo);
        $this->assertSame($mo, $actualMo);
        $this->assertSame($expectedSegments, $actualSegments);

        if (null === $logger) {
            $this->assertInstanceOf(NullLogger::class, $actualLogger);
        } else {
            $this->assertSame($expectedLogger, $actualLogger);
        }
    }

    public function testGetFilesystemProvider()
    {
        // When
        $actual = $this->buildStorage()->getFilesystemProvider();

        // Then
        $this->assertSame($this->fspMock->reveal(), $actual);
    }

    public function saveDataProvider()
    {
        $config1 = ['segment' => 123];
        $config2 = ['foo' => 'bar'];

        return [
            'without storage config parameter' => [55, null],
            'with storage config parameter and defined segment option' => [123, $config1],
            'with storage config parameter and not defined segment option' => [55, $config2],
        ];
    }

    /**
     * @dataProvider saveDataProvider
     *
     * @param int $expectedRand
     * @param $storageOption
     *
     * @throws
     */
    public function testSave($expectedRand, $storageOption)
    {
        // Given
        $tempPath = '/tmp/file.jpeg';
        $fileName = 'file.jpeg';
        $version = 1;

        $expectedSegmentPath = '/'.$expectedRand;
        $expectedFileName = 'file-1.jpeg';
        $expectedFilePath = $expectedSegmentPath.'/'.$expectedFileName;
        $expectedContentPath = '/var/uploads'.$expectedFilePath;
        $expectedResult = json_encode([
            StorageConfig::SEGMENT_OPTION => $expectedRand,
            StorageConfig::FILENAME_OPTION => $expectedFileName,
            StorageConfig::CONTENT_PATH_OPTION => $expectedContentPath,
        ]);

        // Mock MathOverlay::rand()
        if (null === $storageOption || !isset($storageOption['segment'])) {
            $this->mOverlayMock->rand(1, self::DEFAULT_SEGMENTS)->shouldBeCalledTimes(1)->willReturn($expectedRand);
        } else {
            $this->mOverlayMock->rand(Argument::any(), Argument::any())->shouldNotBeCalled(1);
        }
        // End

        // Mock FileResolverInterface::resolveUniqueFileName()
        $this->frMock->resolveUniqueFileName($expectedSegmentPath, $fileName, 0)->shouldBeCalledTimes(1)->willReturn($expectedFileName);
        // End

        // Mock FileResolverInterface::resolveFilePath()
        $this->frMock->resolveFilePath($expectedSegmentPath, $expectedFileName)->shouldBeCalledTimes(1)->willReturn($expectedFilePath);
        // End

        // Mock LoggerInterface::debug()
        $this->loggerMock
            ->debug(sprintf('Read file %s', $tempPath))
            ->shouldBeCalledTimes(1)
        ;
        // End

        // Mock FileOverlay::fileGetContents()
        $fileContent = 'file-content';
        $this->fOverlayMock->fileGetContents($tempPath)->shouldBeCalledTimes(1)->willReturn($fileContent);
        // End

        // Mock LoggerInterface::debug()
        $this->loggerMock
            ->debug(sprintf('Write file to %s', $expectedFilePath))
            ->shouldBeCalledTimes(1)
        ;
        // End

        // Mock FilesystemProviderInterface::write()
        $this->fspMock->write($expectedFilePath, $fileContent)->shouldBeCalledTimes(1)->willReturn(true);
        // End

        // Mock FilesystemProviderInterface::getPathToFileContent()
        $this->fspMock->getPathToFileContent($expectedFilePath)->shouldBeCalledTimes(1)->willReturn($expectedContentPath);
        // End

        // When
        if (null === $storageOption) {
            $actual = $this->buildStorage()->save($tempPath, $fileName, $version);
        } else {
            $actual = $this->buildStorage()->save($tempPath, $fileName, $version, json_encode($storageOption));
        }

        // Then
        $this->assertSame($expectedResult, $actual);
    }

    public function testShouldCallSaveAndThrowFilesystemProviderException()
    {
        // Expect
        $code = 123;
        $msg = 'Test exception message';
        $exceptionMsg = sprintf('Filesystem provider error occurred (%d): %s', $code, $msg);

        $this->expectException(FilesystemProviderInternalException::class);
        $this->expectExceptionCode(FilesystemProviderInternalException::INTERNAL_EXCEPTION_CODE);
        $this->expectExceptionMessage($exceptionMsg);

        // Given
        $tempPath = '/tmp/file.jpeg';
        $fileName = 'file.jpeg';
        $version = 1;

        $expectedRand = 55;
        $expectedSegmentPath = '/'.$expectedRand;
        $expectedFileName = 'file-1.jpeg';
        $expectedFilePath = $expectedSegmentPath.'/'.$expectedFileName;

        // Mock MathOverlay::rand()
        $this->mOverlayMock->rand(1, self::DEFAULT_SEGMENTS)->shouldBeCalledTimes(1)->willReturn($expectedRand);
        // End

        // Mock FileResolverInterface::resolveUniqueFileName()
        $this->frMock->resolveUniqueFileName($expectedSegmentPath, $fileName, 0)->shouldBeCalledTimes(1)->willReturn($expectedFileName);
        // End

        // Mock FileResolverInterface::resolveFilePath()
        $this->frMock->resolveFilePath($expectedSegmentPath, $expectedFileName)->shouldBeCalledTimes(1)->willReturn($expectedFilePath);
        // End

        // Mock LoggerInterface::debug()
        $this->loggerMock
            ->debug(sprintf('Read file %s', $tempPath))
            ->shouldBeCalledTimes(1)
        ;
        // End

        // Mock FileOverlay::fileGetContents()
        $fileContent = 'file-content';
        $this->fOverlayMock->fileGetContents($tempPath)->shouldBeCalledTimes(1)->willReturn($fileContent);
        // End

        // Mock LoggerInterface::debug()
        $this->loggerMock
            ->debug(sprintf('Write file to %s', $expectedFilePath))
            ->shouldBeCalledTimes(1)
        ;
        // End

        // Mock FilesystemProviderInterface::write()
        $exception = new \Exception($msg, $code);
        $this->fspMock->write($expectedFilePath, $fileContent)->shouldBeCalledTimes(1)->willThrow($exception);
        // End

        // Mock LoggerInterface::debug()
        $this->loggerMock
            ->error('Filesystem provider thrown an exception during write file', [
                'exception' => get_class($exception),
                'message' => $msg,
                'code' => $code,
            ])
            ->shouldBeCalledTimes(1)
        ;
        // End

        // When
        $this->buildStorage()->save($tempPath, $fileName, $version);
    }

    public function loadDataProvider()
    {
        $fileName = 'example.jpeg';
        $version = 1;

        $so1 = json_encode([StorageConfig::CONTENT_PATH_OPTION => '/path/file/content/example.jpeg']);
        $so2 = json_encode([StorageConfig::CONTENT_PATH_OPTION => '']);
        $so3 = json_encode([StorageConfig::CONTENT_PATH_OPTION => null]);
        $so4 = json_encode([]);

        return [
            'storage option with correct path to file content' => ['/path/file/content/example.jpeg', $fileName, $version, $so1],
            'storage option with empty path to file content' => [false, $fileName, $version, $so2],
            'storage option with null path to file content' => [false, $fileName, $version, $so3],
            'storage option with no path to file content' => [false, $fileName, $version, $so4],
        ];
    }

    /**
     * @dataProvider loadDataProvider
     *
     * @param $expected
     * @param $fileName
     * @param $version
     * @param $storageOption
     */
    public function testLoad($expected, $fileName, $version, $storageOption)
    {
        // When
        $actual = $this->buildStorage()->load($fileName, $version, $storageOption);

        // Then
        $this->assertSame($expected, $actual);
    }

    public function loadAsStringDataProvider()
    {
        // Dataset 1 - options with segment and filename
        $options1 = ['segment' => 6, 'fileName' => 'example.jpeg'];
        $fileName1 = 'example.jpeg';
        $version1 = 1;
        $expectedSegment1 = '06';
        $expectedPath1 = '/06/example.jpeg';
        $expectedContent1 = 'file-content';
        // End

        // Dataset 2 - options with segment but without filename
        $options2 = ['segment' => 10];
        $fileName2 = 'example.jpeg';
        $version2 = 2;
        $expectedContent2 = false;
        // End

        // Dataset 3 - options with filename but without segment
        $options3 = ['fileName' => 'example.jpeg'];
        $fileName3 = 'example.jpeg';
        $version3 = 3;
        $expectedContent3 = false;
        // End

        // Dataset 4 - options without segment and filename
        $options4 = [];
        $fileName4 = 'example-4.jpeg';
        $version4 = 4;
        $expectedContent4 = false;
        // End

        return [
            'options with segment and filename' => [
                $expectedContent1, $expectedSegment1, $expectedPath1, $fileName1, $version1, $options1,
            ],
            'options with segment but without filename' => [
                $expectedContent2, null, null, $fileName2, $version2, $options2,
            ],
            'options with filename but without segment' => [
                $expectedContent3, null, null, $fileName3, $version3, $options3,
            ],
            'options without segment and filename' => [
                $expectedContent4, null, null, $fileName4, $version4, $options4,
            ],
        ];
    }

    /**
     * @dataProvider loadAsStringDataProvider
     *
     * @param $expectedContent
     * @param $expectedSegment
     * @param $expectedPath
     * @param $fileName
     * @param $version
     * @param $options
     */
    public function testLoadAsString(
        $expectedContent,
        $expectedSegment,
        $expectedPath,
        $fileName,
        $version,
        $options
    ) {
        // Given

        // Mock FileResolverInterface::resolveFilePath()
        if (false !== $expectedContent) {
            $this->frMock
                ->resolveFilePath('/' . $expectedSegment, $fileName)
                ->shouldBeCalledTimes(1)
                ->willReturn($expectedPath);
        } else {
            $this->frMock->resolveFilePath(Argument::any(), Argument::any())->shouldNotBeCalled();
        }
        // End

        // Mock FilesystemProviderInterface::read()
        if (false !== $expectedContent) {
            $this->fspMock->read($expectedPath)->shouldBeCalledTimes(1)->willReturn($expectedContent);
        } else {
            $this->fspMock->read(Argument::any())->shouldNotBeCalled();
        }
        // End

        // When
        $actual = $this->buildStorage()->loadAsString($fileName, $version, json_encode($options));

        // Then
        $this->assertSame($expectedContent, $actual);
    }

    public function testShouldCallLoadAsStringAndReturnFalseWhenFilesystemProviderThrowAnException()
    {
        // Given
        $fileName = 'example.jpeg';
        $version = 1;
        $storageOption = json_encode(['segment' => 5, 'fileName' => $fileName]);

        // Mock FileResolverInterface::resolveFilePath()
        $filePath = '/05/example.jpeg';
        $this->frMock->resolveFilePath('/05', $fileName)->shouldBeCalledTimes(1)->willReturn($filePath);
        // End

        // Mock FilesystemProviderInterface::read()
        $this->fspMock->read($filePath)->shouldBeCalledTimes(1)->willThrow(new \Exception('Test exception'));
        // End

        // Mock LoggerInterface::error()
        $this->loggerMock->error('Original media at path "'.$filePath.'" not found')->shouldBeCalledTimes(1);
        // End

        // When
        $actual = $this->buildStorage()->loadAsString($fileName, $version, $storageOption);

        // Then
        $this->assertFalse($actual);
    }

    public function removeDataProvider()
    {
        // Dataset 1 - options with segment and filename
        $options1 = ['segment' => 6, 'fileName' => 'example.jpeg'];
        $expectedSegment1 = '06';
        $expectedFileName1 = 'example.jpeg';
        $expectedPath1 = '/06/example.jpeg';
        // End

        // Dataset 2 - options with segment but without filename
        $options2 = ['segment' => 6];
        $expectedSegment2 = '06';
        // End

        // Dataset 3 - options with filename but without segment
        $options3 = ['fileName' => 'example.jpeg'];
        $expectedFileName3 = 'example.jpeg';
        // End

        // Dataset 4 - options without segment and filename
        $options4 = [];
        // End

        return [
            'options with segment and filename' => [true, $expectedSegment1, $expectedFileName1, $expectedPath1, $options1],
            'options with segment but without filename' => [false, $expectedSegment2, null, null, $options2],
            'options with filename but without segment' => [false, null, $expectedFileName3, null, $options3],
            'options without segment and filename' => [false, null, null, null, $options4],
        ];
    }

    /**
     * @dataProvider removeDataProvider
     *
     * @param bool $expected
     * @param int $expectedSegment
     * @param string $expectedFileName
     * @param string $expectedPath
     * @param array $options
     */
    public function testRemove($expected, $expectedSegment, $expectedFileName, $expectedPath, array $options)
    {
        // Given

        // Mock FileResolverInterface::resolveFilePath()
        if (true === $expected) {
            $this->frMock
                ->resolveFilePath('/' . $expectedSegment, $expectedFileName)
                ->shouldBeCalledTimes(1)
                ->willReturn($expectedPath);
        } else {
            $this->frMock->resolveFilePath(Argument::any(), Argument::any())->shouldNotBeCalled();
        }
        // End

        // Mock FilesystemProviderInterface::delete()
        if (true === $expected) {
            $this->fspMock->delete($expectedPath)->shouldBeCalledTimes(1);
        } else {
            $this->fspMock->delete(Argument::any())->shouldNotBeCalled();
        }
        // End

        // When
        $actual = $this->buildStorage()->remove(json_encode($options));

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testShouldCallRemoveAndReturnFalseWhenFilesystemProviderThrowAnException()
    {
        // Given
        $options = ['segment' => 6, 'fileName' => 'example.jpeg'];
        $expectedSegment = 6;
        $expectedFileName = 'example.jpeg';
        $expectedPath = '/06/example.jpeg';

        // Mock FileResolverInterface::resolveFilePath()
        $this->frMock
            ->resolveFilePath('/0' . $expectedSegment, $expectedFileName)
            ->shouldBeCalledTimes(1)
            ->willReturn($expectedPath);
        // End

        // Mock FilesystemProviderInterface::delete()
        $this->fspMock->delete($expectedPath)->shouldBeCalledTimes(1)->willThrow(new \Exception('Test exception'));
        // End

        // When
        $actual = $this->buildStorage()->remove(json_encode($options));

        // Then
        $this->assertFalse($actual);
    }

    /**
     * @return PBStorage
     */
    private function buildStorage()
    {
        return new PBStorage(
            $this->fspMock->reveal(),
            $this->frMock->reveal(),
            $this->fOverlayMock->reveal(),
            $this->mOverlayMock->reveal(),
            self::DEFAULT_SEGMENTS,
            $this->loggerMock->reveal()
        );
    }
}
