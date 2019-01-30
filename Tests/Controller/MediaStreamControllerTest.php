<?php

namespace PB\Bundle\SuluStorageBundle\Tests\Controller;

use PB\Bundle\SuluStorageBundle\Controller\MediaStreamController;
use PB\Bundle\SuluStorageBundle\Media\Storage\StorageInterface;
use PB\Bundle\SuluStorageBundle\Tests\Library\Utils\Reflection;
use PB\Component\Overlay\File\FileOverlay;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\MediaBundle\Entity\File;
use Sulu\Bundle\MediaBundle\Entity\FileVersion;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Component\PHPCR\PathCleanupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Paweł Brzeziński <pawel.brzezinski@smartint.pl>
 */
class MediaStreamControllerTest extends TestCase
{
    /** @var ObjectProphecy|ContainerInterface */
    private $containerMock;

    /** @var ObjectProphecy|StorageInterface */
    private $storageMock;

    /** @var ObjectProphecy|PathCleanupInterface */
    private $pcMock;

    /** @var ObjectProphecy|FileOverlay */
    private $foMock;

    /** @var ObjectProphecy|MediaManagerInterface */
    private $mmMock;

    protected function setUp()
    {
        $this->containerMock = $this->prophesize(ContainerInterface::class);
        $this->storageMock = $this->prophesize(StorageInterface::class);
        $this->pcMock = $this->prophesize(PathCleanupInterface::class);
        $this->foMock = $this->prophesize(FileOverlay::class);
        $this->mmMock = $this->prophesize(MediaManagerInterface::class);
    }

    protected function tearDown()
    {
        $this->containerMock = null;
        $this->storageMock = null;
        $this->pcMock = null;
        $this->foMock = null;
        $this->mmMock = null;
    }

    public function mimeTypeDataProvider()
    {
        return [
            'not defined mime type' => ['application/octet-stream', ''],
            'defined mime type' => ['image/jpeg', 'image/jpeg'],
        ];
    }

    /**
     * @dataProvider mimeTypeDataProvider
     *
     *
     * @param $expectedMimeType
     * @param $mimeType
     *
     * @throws \ReflectionException
     */
    public function testShouldCallGetFileResponseAndReturnResponseWithImageForCurrentImageVersion($expectedMimeType, $mimeType)
    {
        // Given
        $locale = 'en';
        $pathToContent = '/path/to/example.jpeg';
        $fileContent = 'test-file-content';

        $fileName = 'example.jpeg';
        $version = 1;
        $fileSize = 100;
        $storageOptions = json_encode(['segment' => 1, 'fileName' => 'example.jpeg', 'contentPath' => $pathToContent]);
        $lastModified = new \DateTime();

        $file = new File();
        $file->setVersion(1);

        $fileVersion = new FileVersion();
        $fileVersion
            ->setFile($file)
            ->setName($fileName)
            ->setSize($fileSize)
            ->setStorageOptions($storageOptions)
            ->setMimeType($mimeType)
            ->setVersion($version)
            ->setCreated($lastModified)
        ;

        // Mock default services
        $this->mockDefaultServices();
        // End

        // Mock StorageInterface::loadAsString()
        $this->storageMock
            ->loadAsString($fileName, $version, $storageOptions)
            ->shouldBeCalledTimes(1)
            ->willReturn($fileContent);
        // End

        // Mock PathCleanupInterface::cleanup()
        $this->pcMock->cleanup('example', $locale)->shouldBeCalledTimes(1)->willReturn('example');
        // End

        $ctrlUnderTest = $this->buildController();
        $methodRef = Reflection::getReflectionMethod(MediaStreamController::class, 'getFileResponse');
        $methodRef->setAccessible(true);

        // When
        /** @var Response $actual */
        $actual = $methodRef->invoke($ctrlUnderTest, $fileVersion, $locale);

        // Then
        $this->assertInstanceOf(Response::class, $actual);
        $this->assertSame(200, $actual->getStatusCode());
        $this->assertSame($fileContent, $actual->getContent());
        $this->assertFalse($actual->headers->has('Link'));
        $this->assertFalse($actual->headers->has('X-Robots-Tag'));
        $this->assertSame($actual->headers->get('Content-Type'), $expectedMimeType);
        $this->assertSame($actual->headers->get('Content-Disposition'), 'attachment; filename="example.jpeg"');
        $this->assertSame($actual->headers->get('Content-Length'), $fileSize);
        $this->assertSame($actual->headers->get('Last-Modified'), $lastModified->format('D, d M Y H:i:s \G\M\T'));
    }

    public function testShouldCallGetFileResponseAndReturnResponseWithImageForNotCurrentImageVersion()
    {
        // Given
        $locale = 'en';
        $pathToContent = '/path/to/example.jpeg';
        $fileContent = 'test-file-content';

        $fileName = 'example.jpeg';
        $mimeType = 'image/jpeg';
        $version = 1;
        $fileSize = 100;
        $storageOptions = json_encode(['segment' => 1, 'fileName' => 'example.jpeg', 'contentPath' => $pathToContent]);
        $lastModified = new \DateTime();

        $media = new Media();
        Reflection::setPropertyValue($media, 'id', 1);

        $file = new File();
        $file
            ->setMedia($media)
            ->setVersion(10);

        $fileVersion = new FileVersion();
        $fileVersion
            ->setFile($file)
            ->setName($fileName)
            ->setSize($fileSize)
            ->setStorageOptions($storageOptions)
            ->setMimeType($mimeType)
            ->setVersion($version)
            ->setCreated($lastModified)
        ;

        $latestFileVersion = new FileVersion();
        $latestFileVersion
            ->setFile($file)
            ->setName($fileName)
            ->setSize($fileSize)
            ->setStorageOptions($storageOptions)
            ->setMimeType($mimeType)
            ->setVersion(10)
            ->setCreated($lastModified)
        ;

        $file->addFileVersion($latestFileVersion);

        // Mock default services
        $this->mockDefaultServices();
        // End

        // Mock StorageInterface::loadAsString()
        $this->storageMock
            ->loadAsString($fileName, $version, $storageOptions)
            ->shouldBeCalledTimes(1)
            ->willReturn($fileContent);
        // End

        // Mock PathCleanupInterface::cleanup()
        $this->pcMock->cleanup('example', $locale)->shouldBeCalledTimes(1)->willReturn('example');
        // End

        // Mock MediaManagerInterface::getUrl()
        $this->mmMock->getUrl(1, $fileName, 10)->shouldBeCalledTimes(1)->willReturn('/media/1/download/example.jpeg?v=10');
        // End

        $ctrlUnderTest = $this->buildController();
        $methodRef = Reflection::getReflectionMethod(MediaStreamController::class, 'getFileResponse');
        $methodRef->setAccessible(true);

        // When
        /** @var Response $actual */
        $actual = $methodRef->invoke($ctrlUnderTest, $fileVersion, $locale);

        // Then
        $this->assertInstanceOf(Response::class, $actual);
        $this->assertSame(200, $actual->getStatusCode());
        $this->assertSame($fileContent, $actual->getContent());
        $this->assertSame($actual->headers->get('Link'), '</media/1/download/example.jpeg?v=10>; rel="canonical"');
        $this->assertSame($actual->headers->get('X-Robots-Tag'), 'noindex, follow');
        $this->assertSame($actual->headers->get('Content-Type'), $mimeType);
        $this->assertSame($actual->headers->get('Content-Disposition'), 'attachment; filename="example.jpeg"');
        $this->assertSame($actual->headers->get('Content-Length'), $fileSize);
        $this->assertSame($actual->headers->get('Last-Modified'), $lastModified->format('D, d M Y H:i:s \G\M\T'));
    }

    public function testShouldCallGetFileResponseAndReturnNotFoundResponseWhenPathToFileContentIsSetToFalse()
    {
        // Given
        $locale = 'en';

        $fileName = 'example.jpeg';
        $version = 1;
        $fileSize = 100;
        $storageOptions = json_encode(['segment' => 1, 'fileName' => 'example.jpeg']);
        $lastModified = new \DateTime();

        $file = new File();
        $file->setVersion(1);

        $fileVersion = new FileVersion();
        $fileVersion
            ->setFile($file)
            ->setName($fileName)
            ->setSize($fileSize)
            ->setStorageOptions($storageOptions)
            ->setMimeType('image/jpeg')
            ->setVersion($version)
            ->setCreated($lastModified)
        ;

        // Mock default services
        $this->mockDefaultServices();
        // End

        // Mock StorageInterface::loadAsString()
        $this->storageMock
            ->loadAsString($fileName, $version, $storageOptions)
            ->shouldBeCalledTimes(1)
            ->willReturn(false);
        // End

        // Mock PathCleanupInterface::cleanup()
        $this->pcMock->cleanup(Argument::any(), Argument::any())->shouldNotBeCalled();
        // End

        $ctrlUnderTest = $this->buildController();
        $methodRef = Reflection::getReflectionMethod(MediaStreamController::class, 'getFileResponse');
        $methodRef->setAccessible(true);

        // When
        /** @var Response $actual */
        $actual = $methodRef->invoke($ctrlUnderTest, $fileVersion, $locale);

        // Then
        $this->assertInstanceOf(Response::class, $actual);
        $this->assertSame(404, $actual->getStatusCode());
        $this->assertSame('File not found', $actual->getContent());
    }

    private function buildController()
    {
        $ctrl = new MediaStreamController();
        $ctrl->setContainer($this->containerMock->reveal());

        return $ctrl;
    }

    private function mockDefaultServices()
    {
        // Mock ContainerInterface::get('sulu_media.storage')
        $this->containerMock->get('sulu_media.storage')->willReturn($this->storageMock->reveal());
        // End

        // Mock ContainerInterface::get('sulu.content.path_cleaner')
        $this->containerMock->get('sulu.content.path_cleaner')->willReturn($this->pcMock->reveal());
        // End

        // Mock ContainerInterface::get('sulu.content.path_cleaner')
        $this->containerMock->get('pb_sulu_storage.file.overlay')->willReturn($this->foMock->reveal());
        // End

        // Mock ContainerInterface::get('ssulu_media.media_manager')
        $this->containerMock->get('sulu_media.media_manager')->willReturn($this->mmMock->reveal());
        // End
    }
}
