<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload proof document for attendance record
     *
     * @param UploadedFile $file
     * @param int $userId
     * @param string $date
     * @return string File path
     * @throws \Exception
     */
    public function uploadProofDocument(UploadedFile $file, int $userId, string $date): string
    {
        // Validate file
        $this->validateFile($file);

        // Generate unique filename
        $filename = $this->generateUniqueFilename($file, $userId, $date);

        // Store file in public disk under attendance/proofs directory
        $filePath = $file->storeAs('attendance/proofs', $filename, 'public');

        if (!$filePath) {
            throw new \Exception('Failed to upload proof document.');
        }

        return $filePath;
    }

    /**
     * Delete proof document file
     *
     * @param string $filePath
     * @return bool
     */
    public function deleteProofDocument(string $filePath): bool
    {
        if (!$filePath) {
            return true;
        }

        return Storage::disk('public')->delete($filePath);
    }

    /**
     * Validate uploaded file
     *
     * @param UploadedFile $file
     * @throws \Exception
     */
    public function validateFile(UploadedFile $file): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload.');
        }

        // Check file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size exceeds maximum limit of 5MB.');
        }

        // Check file type using MIME type
        $allowedMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/jpg', 
            'image/png'
        ];

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new \Exception('Invalid file type. Only PDF, JPG, JPEG, and PNG files are allowed.');
        }

        // Additional security check - validate file extension
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception('Invalid file extension. Only PDF, JPG, JPEG, and PNG files are allowed.');
        }

        // Basic security check - ensure file has content
        if ($file->getSize() === 0) {
            throw new \Exception('Empty file is not allowed.');
        }
    }

    /**
     * Generate unique filename to prevent conflicts
     *
     * @param UploadedFile $file
     * @param int $userId
     * @param string $date
     * @return string
     */
    public function generateUniqueFilename(UploadedFile $file, int $userId, string $date): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $randomString = Str::random(8);
        
        // Format: attendance_proof_{userId}_{date}_{timestamp}_{random}.{extension}
        return "attendance_proof_{$userId}_{$date}_{$timestamp}_{$randomString}.{$extension}";
    }

    /**
     * Get file URL for public access
     *
     * @param string $filePath
     * @return string|null
     */
    public function getFileUrl(string $filePath): ?string
    {
        if (!$filePath) {
            return null;
        }

        return asset('storage/' . $filePath);
    }

    /**
     * Check if file exists
     *
     * @param string $filePath
     * @return bool
     */
    public function fileExists(string $filePath): bool
    {
        if (!$filePath) {
            return false;
        }

        return Storage::disk('public')->exists($filePath);
    }
}