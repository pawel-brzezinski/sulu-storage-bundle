<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Media\FormatManager;

use PB\Bundle\SuluStorageBundle\Media\FormatCache\FormatCacheInterface;
use PB\Bundle\SuluStorageBundle\Media\FormatManager\PBFormatManager;
use PB\Bundle\SuluStorageBundle\Tests\Library\Utils\Reflection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Sulu\Bundle\MediaBundle\Entity\File;
use Sulu\Bundle\MediaBundle\Entity\FileVersion;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\MediaBundle\Media\Exception\ImageProxyMediaNotFoundException;
use Sulu\Bundle\MediaBundle\Media\ImageConverter\ImageConverterInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class PBFormatManagerTest extends TestCase
{
    const RESPONSE_HEADERS = ['Expires' => '+1 month', 'Pragma' => 'public', 'Cache-Control' => 'public'];
    const FORMATS = ['sulu-400x400' => [], 'sulu-260x' => []];
    const SUPPORTED_MIME_TYPES = ['image/*', 'video/*', 'application/pdf'];

    /** @var ObjectProphecy|MediaRepositoryInterface */
    private $mrMock;

    /** @var ObjectProphecy|FormatCacheInterface */
    private $fcMock;

    /** @var ObjectProphecy|ImageConverterInterface */
    private $icMock;

    /** @var ObjectProphecy|LoggerInterface */
    private $lMock;

    protected function setUp()
    {
        $this->mrMock = $this->prophesize(MediaRepositoryInterface::class);
        $this->fcMock = $this->prophesize(FormatCacheInterface::class);
        $this->icMock = $this->prophesize(ImageConverterInterface::class);
        $this->lMock = $this->prophesize(LoggerInterface::class);
    }

    protected function tearDown()
    {
        $this->mrMock = null;
        $this->fcMock = null;
        $this->icMock = null;
        $this->lMock = null;
    }

    public function testShouldReturnLatestFileVersion()
    {
        // Given
        $fileVersion1 = new FileVersion();
        $fileVersion1->setVersion(1);
        $fileVersion2 = new FileVersion();
        $fileVersion2->setVersion(2);
        $file1 = new File();
        $file1->setVersion(2)->addFileVersion($fileVersion1)->addFileVersion($fileVersion2);
        $file2 = new File();
        $file2->setVersion(3);
        $media = new Media();
        $media->addFile($file1)->addFile($file2);

        $managerUnderTest = $this->buildManager(false);
        $methodRef = Reflection::getReflectionMethod($managerUnderTest, 'getLatestFileVersion');
        $methodRef->setAccessible(true);

        // When
        $actual = $methodRef->invoke($managerUnderTest, $media);

        // Then
        $this->assertSame($fileVersion2, $actual);
    }

    public function testShouldCallLatestFileVersionAndThrowImageProxyMediaNotFoundException()
    {
        // Expect
        $this->expectException(ImageProxyMediaNotFoundException::class);
        $this->expectExceptionMessage('Media file version was not found');

        // Given
        $fileVersion1 = new FileVersion();
        $fileVersion1->setVersion(1);
        $fileVersion2 = new FileVersion();
        $fileVersion2->setVersion(2);
        $file1 = new File();
        $file1->setVersion(1);
        $file2 = new File();
        $file2->setVersion(2)->addFileVersion($fileVersion1)->addFileVersion($fileVersion2);
        $media = new Media();
        $media->addFile($file1)->addFile($file2);

        $managerUnderTest = $this->buildManager(false);
        $methodRef = Reflection::getReflectionMethod($managerUnderTest, 'getLatestFileVersion');
        $methodRef->setAccessible(true);

        // When
        $methodRef->invoke($managerUnderTest, $media);
    }

    public function checkMimeTypeSupportedDataProvider()
    {
        return [
            [true, 'image/jpg'],
            [true, 'image/jpeg'],
            [true, 'image/gif'],
            [true, 'video/mp4'],
            [true, 'video/ogg'],
            [true, 'application/pdf'],
            [false, 'application/json'],
            [false, 'text/plain'],
        ];
    }

    /**
     * @dataProvider checkMimeTypeSupportedDataProvider
     *
     * @param bool $expected
     * @param string $mimeType
     *
     * @throws \ReflectionException
     */
    public function testShouldCheckMimeTypeSupported($expected, $mimeType)
    {
        // Given
        $managerUnderTest = $this->buildManager(false);
        $methodRef = Reflection::getReflectionMethod($managerUnderTest, 'checkMimeTypeSupported');
        $methodRef->setAccessible(true);

        // When
        $actual = $methodRef->invoke($managerUnderTest, $mimeType);

        // Then
        $this->assertSame($expected, $actual);
    }


    public function saveImageDataProvider()
    {
        return [
            'save image set on true' => [true],
            'save image set on false' => [false],
        ];
    }

    /**
     * @dataProvider saveImageDataProvider
     *
     * @param $saveImage
     *
     * @throws \ReflectionException
     */
    public function testShouldReturnImageContentWhenImageFormatFileDoesNotExists($saveImage)
    {
        // Given
        $id = 100;
        $formatKey = 'sulu-400x400';
        $fileName = 'example.jpg';
        $mimeType = 'image/jpg';
        $storageOptions = json_encode(['fileName' => $fileName]);

        // Mock MediaRepositoryInterface::findMediaByIdForRendering()
        $fileVersion = new FileVersion();
        $fileVersion->setVersion(1)->setName($fileName)->setMimeType($mimeType)->setStorageOptions($storageOptions);
        $file = new File();
        $file->setVersion(1)->addFileVersion($fileVersion);
        $media = new Media();
        $media->addFile($file);

        $mediaId = 99;
        Reflection::setPropertyValue($media, 'id', $mediaId);

        $this->mrMock->findMediaByIdForRendering($id, $formatKey)->shouldBeCalledTimes(1)->willReturn($media);
        // End

        // Mock FormatCacheInterface::load()
        $this->fcMock->load($id, $fileName, $storageOptions, $formatKey)->shouldBeCalledTimes(1)->willReturn(null);
        // End

        // Mock ImageConverterInterface::convert()
        $convertedContent = file_get_contents(__DIR__.'/__static/example.jpeg');
        $this->icMock->convert($fileVersion, $formatKey)->shouldBeCalledTimes(1)->willReturn($convertedContent);
        // End

        // Mock FormatCacheInterface::save()
        if (true === $saveImage) {
            $this->fcMock
                ->save($convertedContent, $mediaId, $fileName, $storageOptions, $formatKey)
                ->shouldBeCalledTimes(1)
            ;
        } else {
            $this->fcMock
                ->save(Argument::any(), Argument::any(), Argument::any(), Argument::any(), Argument::any())
                ->shouldNotBeCalled()
            ;
        }
        // End

        // When
        $actual = $this->buildManager($saveImage)->returnImage($id, $formatKey);

        // Then
        $this->assertInstanceOf(Response::class, $actual);
        $this->assertSame($convertedContent, $actual->getContent());
        $this->assertSame(200, $actual->getStatusCode());
        $this->assertSame('image/jpeg', $actual->headers->get('Content-Type'));
        $this->assertTrue($actual->headers->has('Expires'));
        $this->assertSame('public', $actual->headers->get('Pragma'));
        $this->assertSame('public', $actual->headers->get('Cache-Control'));
    }

    public function testShouldReturnImageContentWhenImageFormatFileDoesExists()
    {
        // Given
        $id = 100;
        $formatKey = 'sulu-400x400';
        $fileName = 'example.jpg';
        $mimeType = 'image/jpg';
        $storageOptions = json_encode(['fileName' => $fileName]);

        // Mock MediaRepositoryInterface::findMediaByIdForRendering()
        $fileVersion = new FileVersion();
        $fileVersion->setVersion(1)->setName($fileName)->setMimeType($mimeType)->setStorageOptions($storageOptions);
        $file = new File();
        $file->setVersion(1)->addFileVersion($fileVersion);
        $media = new Media();
        $media->addFile($file);

        $mediaId = 99;
        Reflection::setPropertyValue($media, 'id', $mediaId);

        $this->mrMock->findMediaByIdForRendering($id, $formatKey)->shouldBeCalledTimes(1)->willReturn($media);
        // End

        // Mock FormatCacheInterface::load()
        $fileContent = file_get_contents(__DIR__.'/__static/example.jpeg');
        $this->fcMock->load($id, $fileName, $storageOptions, $formatKey)->shouldBeCalledTimes(1)->willReturn($fileContent);
        // End

        // Mock ImageConverterInterface::convert()
        $this->icMock->convert(Argument::any(), Argument::any())->shouldNotBeCalled();
        // End

        // When
        $actual = $this->buildManager(false)->returnImage($id, $formatKey);

        // Then
        $this->assertInstanceOf(Response::class, $actual);
        $this->assertSame($fileContent, $actual->getContent());
        $this->assertSame(200, $actual->getStatusCode());
        $this->assertSame('image/jpeg', $actual->headers->get('Content-Type'));
        $this->assertTrue($actual->headers->has('Expires'));
        $this->assertSame('public', $actual->headers->get('Pragma'));
        $this->assertSame('public', $actual->headers->get('Cache-Control'));
    }

    public function testShouldCallReturnImageAndReturn404ResponseWhenMediaWasNotFound()
    {
        // Given
        $id = 100;
        $formatKey = 'sulu-400x400';

        // Mock MediaRepositoryInterface::findMediaByIdForRendering()
        $this->mrMock->findMediaByIdForRendering($id, $formatKey)->shouldBeCalledTimes(1)->willReturn(null);
        // End

        // Mock LoggerInterface::error()
        $this->lMock->error('Media was not found', Argument::type('array'))->shouldBeCalledTimes(1);
        // End

        // When
        $actual = $this->buildManager(false)->returnImage($id, $formatKey);

        // Then
        $this->assertInstanceOf(Response::class, $actual);
        $this->assertSame('', $actual->getContent());
        $this->assertSame(404, $actual->getStatusCode());
        $this->assertSame(null, $actual->headers->get('Expires'));
        $this->assertSame(null, $actual->headers->get('Pragma'));
        $this->assertSame('no-cache, private', $actual->headers->get('Cache-Control'));
    }

    public function testShouldCallReturnImageAndReturn404ResponseWhenMimeTypeIsNotSupported()
    {
        // Given
        $id = 100;
        $formatKey = 'sulu-400x400';
        $fileName = 'example.jpg';
        $mimeType = 'text/plain';
        $storageOptions = json_encode(['fileName' => $fileName]);

        // Mock MediaRepositoryInterface::findMediaByIdForRendering()
        $fileVersion = new FileVersion();
        $fileVersion->setVersion(1)->setName($fileName)->setMimeType($mimeType)->setStorageOptions($storageOptions);
        $file = new File();
        $file->setVersion(1)->addFileVersion($fileVersion);
        $media = new Media();
        $media->addFile($file);

        $mediaId = 99;
        Reflection::setPropertyValue($media, 'id', $mediaId);

        $this->mrMock->findMediaByIdForRendering($id, $formatKey)->shouldBeCalledTimes(1)->willReturn($media);
        // End

        // Mock FormatCacheInterface::load()
        $this->fcMock->load($id, $fileName, $storageOptions, $formatKey)->shouldBeCalledTimes(1)->willReturn(null);
        // End

        // Mock LoggerInterface::error()
        $this->lMock
            ->error('The mimeType "text/plain" is not supported for preview.', Argument::type('array'))
            ->shouldBeCalledTimes(1)
        ;
        // End

        // When
        $actual = $this->buildManager(false)->returnImage($id, $formatKey);

        // Then
        $this->assertInstanceOf(Response::class, $actual);
        $this->assertSame('', $actual->getContent());
        $this->assertSame(404, $actual->getStatusCode());
        $this->assertSame(null, $actual->headers->get('Expires'));
        $this->assertSame(null, $actual->headers->get('Pragma'));
        $this->assertSame('no-cache, private', $actual->headers->get('Cache-Control'));
    }

    /**
     * @param bool $saveImage
     *
     * @return PBFormatManager
     */
    private function buildManager($saveImage)
    {
        return new PBFormatManager(
            $this->mrMock->reveal(),
            $this->fcMock->reveal(),
            $this->icMock->reveal(),
            $saveImage,
            self::RESPONSE_HEADERS,
            self::FORMATS,
            self::SUPPORTED_MIME_TYPES,
            $this->lMock->reveal()
        );
    }
}
