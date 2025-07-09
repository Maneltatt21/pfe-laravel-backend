<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload a document file.
     */
    public function uploadDocument(UploadedFile $file): string
    {
        $this->validateDocument($file);
        
        $filename = $this->generateFilename($file, 'doc');
        $path = $file->storeAs('documents', $filename, 'public');
        
        return $path;
    }

    /**
     * Upload an invoice file.
     */
    public function uploadInvoice(UploadedFile $file): string
    {
        $this->validateInvoice($file);
        
        $filename = $this->generateFilename($file, 'inv');
        $path = $file->storeAs('invoices', $filename, 'public');
        
        return $path;
    }

    /**
     * Upload a photo file.
     */
    public function uploadPhoto(UploadedFile $file): string
    {
        $this->validatePhoto($file);
        
        $filename = $this->generateFilename($file, 'photo');
        $path = $file->storeAs('exchange_photos', $filename, 'public');
        
        return $path;
    }

    /**
     * Delete a file from storage.
     */
    public function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        
        return true;
    }

    /**
     * Get the full URL for a file.
     */
    public function getFileUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    /**
     * Validate document file.
     */
    private function validateDocument(UploadedFile $file): void
    {
        $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 2048; // 2MB in KB

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('Invalid file type. Only PDF, JPG, JPEG, and PNG files are allowed.');
        }

        if ($file->getSize() > $maxSize * 1024) {
            throw new \InvalidArgumentException('File size too large. Maximum size is 2MB.');
        }
    }

    /**
     * Validate invoice file.
     */
    private function validateInvoice(UploadedFile $file): void
    {
        $this->validateDocument($file); // Same validation as documents
    }

    /**
     * Validate photo file.
     */
    private function validatePhoto(UploadedFile $file): void
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 2048; // 2MB in KB

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('Invalid file type. Only JPG, JPEG, and PNG files are allowed.');
        }

        if ($file->getSize() > $maxSize * 1024) {
            throw new \InvalidArgumentException('File size too large. Maximum size is 2MB.');
        }
    }

    /**
     * Generate a unique filename.
     */
    private function generateFilename(UploadedFile $file, string $prefix = ''): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);
        
        return $prefix ? "{$prefix}_{$timestamp}_{$random}.{$extension}" : "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get file size in human readable format.
     */
    public function getFileSize(string $path): string
    {
        if (!Storage::disk('public')->exists($path)) {
            return 'File not found';
        }

        $bytes = Storage::disk('public')->size($path);
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Check if file exists.
     */
    public function fileExists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }
}
