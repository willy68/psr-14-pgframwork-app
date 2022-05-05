<?php
namespace Tests\Framework;

use Framework\Upload;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

class UploadTest extends TestCase
{

    /**
     * @var Upload
     */
    private $upload;

    public function setUp(): void
    {
        $this->upload = new Upload('tests');
    }

    public function tearDown(): void
    {
        if (file_exists('tests/demo.jpg')) {
            unlink('tests/demo.jpg');
        }
    }

    public function testUpload()
    {
        $uploadedFile = $this->getMockBuilder(UploadedFileInterface::class)->getMock();

        $uploadedFile->expects($this->any())
            ->method('getError')
            ->willReturn(UPLOAD_ERR_OK);

        $uploadedFile->expects($this->any())
            ->method('getClientFilename')
            ->willReturn('demo.jpg');

        $uploadedFile->expects($this->once())
            ->method('moveTo')
            ->with($this->equalTo('tests' . DIRECTORY_SEPARATOR . 'demo.jpg'));

        $this->assertEquals('demo.jpg', $this->upload->upload($uploadedFile));
    }

    public function testDontMoveIfFileNotUploaded()
    {
        $uploadedFile = $this->getMockBuilder(UploadedFileInterface::class)->getMock();

        $uploadedFile->expects($this->any())
            ->method('getError')
            ->willReturn(UPLOAD_ERR_CANT_WRITE);

        $uploadedFile->expects($this->any())
            ->method('getClientFilename')
            ->willReturn('demo.jpg');

        $uploadedFile->expects($this->never())
            ->method('moveTo')
            ->with($this->equalTo('tests' . DIRECTORY_SEPARATOR . 'demo.jpg'));

        $this->assertNull($this->upload->upload($uploadedFile));
    }

    public function testUploadWithExistingFile()
    {
        $uploadedFile = $this->getMockBuilder(UploadedFileInterface::class)->getMock();

        $uploadedFile->expects($this->any())
            ->method('getError')
            ->willReturn(UPLOAD_ERR_OK);

        $uploadedFile->expects($this->any())
            ->method('getClientFilename')
            ->willReturn('demo.jpg');

        touch('tests' . DIRECTORY_SEPARATOR . 'demo.jpg');

        $uploadedFile->expects($this->once())
            ->method('moveTo')
            ->with($this->equalTo('tests' . DIRECTORY_SEPARATOR . 'demo_copy.jpg'));

        $this->assertEquals('demo_copy.jpg', $this->upload->upload($uploadedFile));
    }

    public function testDoNothingIfFileNotUploaded()
    {
        $file = $this->getMockBuilder(UploadedFileInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $file->method('getError')->willReturn(UPLOAD_ERR_CANT_WRITE);
        $file->expects($this->never())->method('moveTo');
        $this->upload->upload($file);
    }

    public function testCreateFormats()
    {
        @unlink('tests/demo.png');
        @unlink('tests/demo_thumb.png');
        $file = $this->getMockBuilder(UploadedFileInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $file->method('getError')->willReturn(UPLOAD_ERR_OK);
        $file->method('getClientFileName')->willReturn('demo.png');
        $file->expects($this->once())->method('moveTo')->willReturnCallback(function () {
            imagepng(imagecreatetruecolor(1000, 1000), 'tests' . DIRECTORY_SEPARATOR . 'demo.png');
        });
        // On crée un faux format
        $property = (new \ReflectionClass($this->upload))->getProperty('formats');
        $property->setAccessible(true);
        $property->setValue($this->upload, ['thumb' => [100, 200]]);
        // On s'attend à obtenir une image miniature
        $this->upload->upload($file);
        $this->assertTrue(in_array(100, getimagesize('tests' . DIRECTORY_SEPARATOR . 'demo_thumb.png')));
        $this->assertTrue(in_array(200, getimagesize('tests' . DIRECTORY_SEPARATOR . 'demo_thumb.png')));
        $this->assertFileExists('tests/demo_thumb.png');
        @unlink('tests/demo.png');
        @unlink('tests/demo_thumb.png');
    }

    public function testDeleteOldFormat()
    {
        // On crée un faux format
        $property = (new \ReflectionClass($this->upload))->getProperty('formats');
        $property->setAccessible(true);
        $property->setValue($this->upload, ['thumb' => [100, 200]]);
        // On s'attend à obtenir une image miniature
        touch('tests/demo.png');
        touch('tests/demo_thumb.png');
        $this->upload->delete('demo.png');
        $this->assertFileNotExists('tests/demo.png');
        $this->assertFileNotExists('tests/demo_thumb.png');
        @unlink('tests/demo.png');
        @unlink('tests/demo_thumb.png');
    }
}
