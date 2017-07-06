<?php

namespace PB\Bundle\SuluStorageBundle\Tests\HttpFoundation;

use League\Flysystem\File as FlysystemFile;
use PB\Bundle\SuluStorageBundle\HttpFoundation\BinaryFlysystemFileManagerResponse;
use PB\Bundle\SuluStorageBundle\HttpFoundation\Exception\BadFileManagerInstanceException;
use PB\Bundle\SuluStorageBundle\Tests\AbstractTests;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * This tests are based on Symfony tests for standard Symfony BinaryFileResponse.
 */
class BinaryFlysystemFileManagerResponseTest extends AbstractTests
{
    protected $filePath = __DIR__.'/../app/Resources/test.gif';
    protected $fileMetaData = [];

    public function setUp()
    {
        $this->fileMetaData = pathinfo($this->filePath);
        $this->fileMetaData['size'] = filesize($this->filePath);
    }

    public function provideConstructionFilesPath()
    {
        return [
            [__DIR__.'/../app/Resources/test.gif'],
            [__DIR__.'/../app/Resources/tęśt.gif'],
        ];
    }

    /**
     * @dataProvider provideConstructionFilesPath
     */
    public function testConstruction($filePath)
    {
        $fileMock = $this->getStandardFileMock($filePath, 1, 2, 1);
        $managerMock = $this->getStandardManagerMock($fileMock, 2);
        $cleanerMock = $this->generatePathCleanerMock();

        $response = new BinaryFlysystemFileManagerResponse(
            $managerMock,
            404,
            ['X-Header' => 'Foo'],
            true,
            null,
            true,
            true,
            $cleanerMock
        );
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Foo', $response->headers->get('X-Header'));
        $this->assertTrue($response->headers->has('ETag'));
        $this->assertTrue($response->headers->has('Last-Modified'));
        $this->assertFalse($response->headers->has('Content-Disposition'));


        $response = BinaryFlysystemFileManagerResponse::create(
            $managerMock,
            404,
            [],
            true,
            ResponseHeaderBag::DISPOSITION_INLINE
        );
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertFalse($response->headers->has('ETag'));
        $this->assertEquals('inline; filename="test.gif"', $response->headers->get('Content-Disposition'));
    }

    public function testSetFileWithWrongManagerInstance()
    {
        $fileMock = $this->getStandardFileMock($this->filePath);

        $this->expectException(BadFileManagerInstanceException::class);
        new BinaryFlysystemFileManagerResponse($fileMock);
    }

    public function testGetFile()
    {
        $fileMock = $this->getStandardFileMock($this->filePath);
        $managerMock = $this->getStandardManagerMock($fileMock, 1);
        $response = BinaryFlysystemFileManagerResponse::create($managerMock);

        $file = $response->getFile();

        $this->assertInstanceOf(FlysystemFile::class, $file);
        $this->assertEquals($fileMock, $file);
    }

    public function testSetContentDispositionWhenFilenameCannotBeResolved()
    {
        $fileMock = $this->generateFlysystemFileMock();
        $fileMock
            ->expects($this->once())
            ->method('getMetadata')
            ->willReturn([]);

        $managerMock = $this->getStandardManagerMock($fileMock, 1);
        $response = BinaryFlysystemFileManagerResponse::create($managerMock);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        $this->assertFalse($response->headers->has('Content-Disposition'));
    }

    public function testPrepareWhenFileNotHasMimeType()
    {
        $fileMock = $this->generateFlysystemFileMock();
        $fileMock
            ->expects($this->once())
            ->method('getMimeType')
            ->willReturn('');
        $managerMock = $this->getStandardManagerMock($fileMock, 1);
        $response = BinaryFlysystemFileManagerResponse::create($managerMock);

        $request = Request::create('/');
        $response->prepare($request);

        $this->assertEquals('application/octet-stream', $response->headers->get('Content-Type'));
    }

    public function testPrepareWhenFileNotHasSize()
    {
        $fileMock = $this->generateFlysystemFileMock();
        $fileMock
            ->expects($this->once())
            ->method('getMimeType')
            ->willReturn('');
        $fileMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn(false);
        $managerMock = $this->getStandardManagerMock($fileMock, 1);
        $response = BinaryFlysystemFileManagerResponse::create($managerMock);

        $request = Request::create('/');
        $response->prepare($request);

        $this->assertFalse($response->headers->has('Content-Length'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetContent()
    {
        $fileMock = $this->getStandardFileMock($this->filePath, 0, 1, 0);
        $managerMock = $this->getStandardManagerMock($fileMock, 1);

        $response = new BinaryFlysystemFileManagerResponse($managerMock);
        $response->setContent('foo');
    }

    public function testGetContent()
    {
        $fileMock = $this->getStandardFileMock($this->filePath, 0, 1, 0);
        $managerMock = $this->getStandardManagerMock($fileMock, 1);

        $response = new BinaryFlysystemFileManagerResponse($managerMock);
        $this->assertFalse($response->getContent());
    }

    public function testSetContentDispositionGeneratesSafeFallbackFilename()
    {
        $fileMock = $this->getStandardFileMock($this->filePath, 0, 1, 0);
        $managerMock = $this->getStandardManagerMock($fileMock, 1);

        $cleanerMock = $this->generatePathCleanerMock();
        $cleanerMock
            ->expects($this->once())
            ->method('cleanup')
            ->willReturn('foo');

        $response = new BinaryFlysystemFileManagerResponse($managerMock);
        $response->setPathCleaner($cleanerMock, 'pl');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'fóó.html');

        $this->assertSame('attachment; filename="foo.html"; filename*=utf-8\'\'f%C3%B3%C3%B3.html', $response->headers->get('Content-Disposition'));
    }

    public function provideRanges()
    {
        return array(
            array('bytes=1-4', 1, 4, 'bytes 1-4/35'),
            array('bytes=-5', 30, 5, 'bytes 30-34/35'),
            array('bytes=30-', 30, 5, 'bytes 30-34/35'),
            array('bytes=30-30', 30, 1, 'bytes 30-30/35'),
            array('bytes=30-34', 30, 5, 'bytes 30-34/35'),
        );
    }

    /**
     * @dataProvider provideRanges
     */
    public function testRequests($requestRange, $offset, $length, $responseRange)
    {
        $fsMock = $this->generateFilesystemMock();
        $fsMock
            ->expects($this->once())
            ->method('readStream')
            ->willReturn(fopen($this->filePath, 'r'));

        $fileMock = $this->getStandardFileMock($this->filePath, 0, 1, 0, 2);
        $fileMock
            ->expects($this->once())
            ->method('getFilesystem')
            ->willReturn($fsMock);
        $fileMock
            ->expects($this->once())
            ->method('getPath')
            ->willReturn($this->filePath);

        $managerMock = $this->getStandardManagerMock($fileMock, 1);

        $response = BinaryFlysystemFileManagerResponse::create(
            $managerMock,
            200,
            ['Content-Type' => 'application/octet-stream']
        )->setAutoEtag();

        // do a request to get the ETag
        $request = Request::create('/');
        $response->prepare($request);
        $etag = $response->headers->get('ETag');

        // prepare a request for a range of the testing file
        $request = Request::create('/');
        $request->headers->set('If-Range', $etag);
        $request->headers->set('Range', $requestRange);

        $file = fopen($this->filePath, 'r');
        fseek($file, $offset);
        $data = fread($file, $length);
        fclose($file);

        $this->expectOutputString($data);
        $response = clone $response;
        $response->prepare($request);
        $response->sendContent();

        $this->assertEquals(206, $response->getStatusCode());
        $this->assertEquals($responseRange, $response->headers->get('Content-Range'));
        $this->assertSame($length, $response->headers->get('Content-Length'));
    }

    /**
     * @dataProvider provideRanges
     */
    public function testRequestsWithoutEtag($requestRange, $offset, $length, $responseRange)
    {
        $fsMock = $this->generateFilesystemMock();
        $fsMock
            ->expects($this->once())
            ->method('readStream')
            ->willReturn(fopen($this->filePath, 'r'));

        $fileMock = $this->getStandardFileMock($this->filePath, 0, 1, 0, 2);
        $fileMock
            ->expects($this->once())
            ->method('getFilesystem')
            ->willReturn($fsMock);
        $fileMock
            ->expects($this->once())
            ->method('getPath')
            ->willReturn($this->filePath);

        $managerMock = $this->getStandardManagerMock($fileMock, 1);

        $response = BinaryFlysystemFileManagerResponse::create(
            $managerMock,
            200,
            ['Content-Type' => 'application/octet-stream']
        );

        // do a request to get the LastModified
        $request = Request::create('/');
        $response->prepare($request);
        $lastModified = $response->headers->get('Last-Modified');

        // prepare a request for a range of the testing file
        $request = Request::create('/');
        $request->headers->set('If-Range', $lastModified);
        $request->headers->set('Range', $requestRange);

        $file = fopen($this->filePath, 'r');
        fseek($file, $offset);
        $data = fread($file, $length);
        fclose($file);

        $this->expectOutputString($data);
        $response = clone $response;
        $response->prepare($request);
        $response->sendContent();

        $this->assertEquals(206, $response->getStatusCode());
        $this->assertEquals($responseRange, $response->headers->get('Content-Range'));
    }

    public function testRangeRequestsWithoutLastModifiedDate()
    {
        $fsMock = $this->generateFilesystemMock();
        $fsMock
            ->expects($this->once())
            ->method('readStream')
            ->willReturn(fopen($this->filePath, 'r'));

        $fileMock = $this->getStandardFileMock($this->filePath, 0, 0, 0, 1);
        $fileMock
            ->expects($this->once())
            ->method('getFilesystem')
            ->willReturn($fsMock);
        $fileMock
            ->expects($this->once())
            ->method('getPath')
            ->willReturn($this->filePath);

        $managerMock = $this->getStandardManagerMock($fileMock, 1);

        $response = BinaryFlysystemFileManagerResponse::create(
            $managerMock,
            200,
            ['Content-Type' => 'application/octet-stream'],
            true,
            null,
            false,
            false
        );

        // prepare a request for a range of the testing file
        $request = Request::create('/');
        $request->headers->set('If-Range', date('D, d M Y H:i:s').' GMT');
        $request->headers->set('Range', 'bytes=1-4');

        $this->expectOutputString(file_get_contents($this->filePath));
        $response = clone $response;
        $response->prepare($request);
        $response->sendContent();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNull($response->headers->get('Content-Range'));
    }

    public function provideFullFileRanges()
    {
        return array(
            array('bytes=0-'),
            array('bytes=0-34'),
            array('bytes=-35'),
            // Syntactical invalid range-request should also return the full resource
            array('bytes=20-10'),
            array('bytes=50-40'),
        );
    }

    /**
     * @dataProvider provideFullFileRanges
     */
    public function testFullFileRequests($requestRange)
    {
        $fsMock = $this->generateFilesystemMock();
        $fsMock
            ->expects($this->once())
            ->method('readStream')
            ->willReturn(fopen($this->filePath, 'r'));

        $fileMock = $this->getStandardFileMock($this->filePath, 0, 1, 0, 1);
        $fileMock
            ->expects($this->once())
            ->method('getFilesystem')
            ->willReturn($fsMock);
        $fileMock
            ->expects($this->once())
            ->method('getPath')
            ->willReturn($this->filePath);

        $managerMock = $this->getStandardManagerMock($fileMock, 1);

        $response = BinaryFlysystemFileManagerResponse::create(
            $managerMock,
            200,
            ['Content-Type' => 'application/octet-stream']
        )->setAutoEtag();

        // prepare a request for a range of the testing file
        $request = Request::create('/');
        $request->headers->set('Range', $requestRange);

        $file = fopen($this->filePath, 'r');
        $data = fread($file, 35);
        fclose($file);

        $this->expectOutputString($data);
        $response = clone $response;
        $response->prepare($request);
        $response->sendContent();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function provideInvalidRanges()
    {
        return array(
            array('bytes=-40'),
            array('bytes=30-40'),
        );
    }

    /**
     * @dataProvider provideInvalidRanges
     */
    public function testInvalidRequests($requestRange)
    {
        $fileMock = $this->getStandardFileMock($this->filePath, 0, 1, 0, 1);
        $managerMock = $this->getStandardManagerMock($fileMock, 1);

        $response = BinaryFlysystemFileManagerResponse::create(
            $managerMock,
            200,
            ['Content-Type' => 'application/octet-stream']
        )->setAutoEtag();

        // prepare a request for a range of the testing file
        $request = Request::create('/');
        $request->headers->set('Range', $requestRange);

        $response = clone $response;
        $response->prepare($request);
        $response->sendContent();

        $this->assertEquals(416, $response->getStatusCode());
        $this->assertEquals('bytes */35', $response->headers->get('Content-Range'));
    }

    public function provideXSendfileFiles()
    {
        return array(
            [$this->filePath],
            array('file://' . $this->filePath),
        );
    }

    /**
     * @dataProvider provideXSendfileFiles
     */
    public function testXSendfile($file)
    {
        $fsMock = $this->generateFilesystemMock();
        $fileMock = $this->getStandardFileMock($this->filePath, 0, 0, 0, 1);
        $fileMock
            ->expects($this->once())
            ->method('getFilesystem')
            ->willReturn($fsMock);
        $fileMock
            ->expects($this->once())
            ->method('getPath')
            ->willReturn($file);

        $urlResolverMock = $this->generateUrlResolverMock();
        $urlResolverMock
            ->expects($this->once())
            ->method('getUrl')
            ->willReturn('http://example.com/test.gif');

        $managerMock = $this->getStandardManagerMock($fileMock, 1);
        $managerMock
            ->expects($this->once())
            ->method('getUrlResolver')
            ->willReturn($urlResolverMock);

        BinaryFlysystemFileManagerResponse::trustXSendfileTypeHeader();
        $response = BinaryFlysystemFileManagerResponse::create(
            $managerMock,
            200,
            ['Content-Type' => 'application/octet-stream']
        );

        $request = Request::create('/');
        $request->headers->set('X-Sendfile-Type', 'X-Sendfile');

        $response->prepare($request);

        $this->expectOutputString('');
        $response->sendContent();
    }

    public function getSampleXAccelMappings()
    {
        return array(
            array('/var/www/var/www/files/foo.txt', '/var/www/=/files/', '/files/var/www/files/foo.txt'),
            array('/home/foo/bar.txt', '/var/www/=/files/,/home/foo/=/baz/', '/baz/bar.txt'),
            array('/var/uploads/media/foo.txt', '/var/uploads/=/uploads/', '/uploads/media/foo.txt'),
        );
    }

    /**
     * @dataProvider getSampleXAccelMappings
     */
    public function testXAccelMapping($realpath, $mapping, $virtual)
    {
        $fsMock = $this->generateFilesystemMock();
        $fileMock = $this->getStandardFileMock($this->filePath, 0, 0, 0, 1);
        $fileMock
            ->expects($this->once())
            ->method('getFilesystem')
            ->willReturn($fsMock);

        $urlResolverMock = $this->generateUrlResolverMock();
        $urlResolverMock
            ->expects($this->once())
            ->method('getUrl')
            ->willReturn($realpath);

        $managerMock = $this->getStandardManagerMock($fileMock, 1);
        $managerMock
            ->expects($this->once())
            ->method('getUrlResolver')
            ->willReturn($urlResolverMock);

        BinaryFlysystemFileManagerResponse::trustXSendfileTypeHeader();
        $response = BinaryFlysystemFileManagerResponse::create(
            $managerMock,
            200,
            ['Content-Type' => 'application/octet-stream']
        );

        $request = Request::create('/');
        $request->headers->set('X-Sendfile-Type', 'X-Accel-Redirect');
        $request->headers->set('X-Accel-Mapping', $mapping);

        $response->prepare($request);
        $this->assertEquals($virtual, $response->headers->get('X-Accel-Redirect'));
    }

    public function testDeleteFileAfterSend()
    {
        $path = __DIR__ . '/../app/Resources/to_delete';
        touch($path);
        $realPath = realpath($path);
        $this->assertFileExists($realPath);

        $fsMock = $this->generateFilesystemMock();
        $fsMock
            ->expects($this->once())
            ->method('readStream')
            ->willReturn(fopen($path, 'r'));
        $fsMock
            ->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $fileMock = $this->getStandardFileMock($path, 0, 1, 0, 1);
        $fileMock
            ->expects($this->exactly(2))
            ->method('getFilesystem')
            ->willReturn($fsMock);
        $fileMock
            ->expects($this->exactly(2))
            ->method('getPath')
            ->willReturn($path);

        $managerMock = $this->getStandardManagerMock($fileMock, 1);

        $response = BinaryFlysystemFileManagerResponse::create(
            $managerMock,
            200,
            ['Content-Type' => 'application/octet-stream']
        );
        $response->deleteFileAfterSend(true);

        $request = Request::create('/');
        $response->prepare($request);
        $response->sendContent();

        unlink($path);
        $this->assertFileNotExists($path);
    }

    public function testAcceptRangeOnUnsafeMethods()
    {
        $fileMock = $this->getStandardFileMock($this->filePath, 0, 0, 0, 1);
        $managerMock = $this->getStandardManagerMock($fileMock, 1);

        BinaryFlysystemFileManagerResponse::trustXSendfileTypeHeader();
        $response = BinaryFlysystemFileManagerResponse::create(
            $managerMock,
            200,
            ['Content-Type' => 'application/octet-stream']
        );

        $request = Request::create('/', 'POST');
        $response->prepare($request);

        $this->assertEquals('none', $response->headers->get('Accept-Ranges'));
    }

    public function testAcceptRangeNotOverriden()
    {
        $fileMock = $this->getStandardFileMock($this->filePath, 0, 0, 0, 1);
        $managerMock = $this->getStandardManagerMock($fileMock, 1);

        BinaryFlysystemFileManagerResponse::trustXSendfileTypeHeader();
        $response = BinaryFlysystemFileManagerResponse::create(
            $managerMock,
            200,
            ['Content-Type' => 'application/octet-stream']
        );
        $response->headers->set('Accept-Ranges', 'foo');

        $request = Request::create('/', 'POST');
        $response->prepare($request);

        $this->assertEquals('foo', $response->headers->get('Accept-Ranges'));
    }

    public function testResolveFilePath()
    {
        $fsMock = $this->generateFilesystemMock();

        $fileMock = $this->getStandardFileMock($this->filePath);
        $fileMock
            ->expects($this->once())
            ->method('getFilesystem')
            ->willReturn($fsMock);

        $urlResolver = $this->generateUrlResolverMock();
        $urlResolver
            ->expects($this->once())
            ->method('getUrl')
            ->willReturn('http://example.com/test.gif');

        $managerMock = $this->getStandardManagerMock($fileMock, 1);
        $managerMock
            ->expects($this->once())
            ->method('getUrlResolver')
            ->willReturn($urlResolver);

        $response = BinaryFlysystemFileManagerResponse::create(
            $managerMock,
            200,
            [],
            true,
            null,
            false,
            false
        );

        $reflection = new \ReflectionClass(BinaryFlysystemFileManagerResponse::class);
        $method = $reflection->getMethod('resolveFilePath');
        $method->setAccessible(true);

        $this->assertEquals('http://example.com/test.gif', $method->invokeArgs($response, []));
    }

    protected function getStandardFileMock(
        $filePath,
        $readExpect = 0,
        $getTimestampExpect = 0,
        $getMetadataExpect = 0,
        $getSizeExpect = 0
    ) {
        $fileMock = $this->generateFlysystemFileMock();

        if ($readExpect > 0) {
            $fileMock
                ->expects($this->exactly($readExpect))
                ->method('read')
                ->willReturn(file_get_contents($filePath));
        }

        if ($getTimestampExpect > 0) {
            $fileMock
                ->expects($this->exactly($getTimestampExpect))
                ->method('getTimestamp')
                ->willReturn(time());
        }

        if ($getMetadataExpect > 0) {
            $fileMock
                ->expects($this->exactly($getMetadataExpect))
                ->method('getMetadata')
                ->willReturn($this->fileMetaData);
        }

        if ($getSizeExpect > 0) {
            $fileMock
                ->expects($this->exactly($getSizeExpect))
                ->method('getSize')
                ->willReturn($this->fileMetaData['size']);
        }

        return $fileMock;
    }

    protected function getStandardManagerMock($fileMock, $getFileExpect = 0)
    {
        $managerMock = $this->generateFlysystemFileManagerMock();

        if ($getFileExpect > 0) {
            $managerMock
                ->expects($this->exactly($getFileExpect))
                ->method('getFile')
                ->willReturn($fileMock);
        }

        return $managerMock;
    }
}
