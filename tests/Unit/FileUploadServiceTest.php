<?php

namespace Tests\Unit;

use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadServiceTest extends TestCase
{
    protected $fileUploadService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileUploadService = new FileUploadService();
        
        // Use fake storage for testing
        Storage::fake('public');
    }

    public function test_can_upload_proof_document()
    {
        // Create a fake file
        $file = UploadedFile::fake()->image('proof.jpg', 100, 100)->size(1024); // 1MB

        $userId = 1;
        $date = '2025-01-15';

        // Upload the file
        $filePath = $this->fileUploadService->uploadProofDocument($file, $userId, $date);

        // Assert file was stored
        $this->assertNotNull($filePath);
        $this->assertTrue(str_contains($filePath, 'attendance/proofs/'));
        Storage::disk('public')->assertExists($filePath);
    }

    public function test_validates_file_size()
    {
        // Create a file that's too large (6MB)
        $file = UploadedFile::fake()->image('large.jpg')->size(6144); // 6MB

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File size exceeds maximum limit of 5MB');

        $this->fileUploadService->uploadProofDocument($file, 1, '2025-01-15');
    }

    public function test_validates_file_type()
    {
        // Create an invalid file type
        $file = UploadedFile::fake()->create('document.txt', 100);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid file type');

        $this->fileUploadService->uploadProofDocument($file, 1, '2025-01-15');
    }

    public function test_can_delete_proof_document()
    {
        // Create and upload a file first
        $file = UploadedFile::fake()->image('proof.jpg');
        $filePath = $this->fileUploadService->uploadProofDocument($file, 1, '2025-01-15');

        // Verify file exists
        Storage::disk('public')->assertExists($filePath);

        // Delete the file
        $result = $this->fileUploadService->deleteProofDocument($filePath);

        // Assert file was deleted
        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($filePath);
    }

    public function test_generates_unique_filename()
    {
        $file = UploadedFile::fake()->image('proof.jpg');
        $userId = 1;
        $date = '2025-01-15';

        $filename1 = $this->fileUploadService->generateUniqueFilename($file, $userId, $date);
        $filename2 = $this->fileUploadService->generateUniqueFilename($file, $userId, $date);

        // Filenames should be different due to timestamp and random string
        $this->assertNotEquals($filename1, $filename2);
        $this->assertTrue(str_contains($filename1, "attendance_proof_{$userId}_{$date}"));
        $this->assertTrue(str_contains($filename2, "attendance_proof_{$userId}_{$date}"));
    }

    public function test_validates_empty_file()
    {
        // Create an empty file
        $file = UploadedFile::fake()->create('empty.pdf', 0);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Empty file is not allowed');

        $this->fileUploadService->uploadProofDocument($file, 1, '2025-01-15');
    }

    public function test_accepts_valid_file_types()
    {
        $validFiles = [
            UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->image('image.jpg'),
            UploadedFile::fake()->image('image.jpeg'),
            UploadedFile::fake()->image('image.png')
        ];

        foreach ($validFiles as $file) {
            $filePath = $this->fileUploadService->uploadProofDocument($file, 1, '2025-01-15');
            $this->assertNotNull($filePath);
            Storage::disk('public')->assertExists($filePath);
        }
    }
}