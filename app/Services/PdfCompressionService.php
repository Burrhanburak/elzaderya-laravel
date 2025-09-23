<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\PdfOptimizer\Laravel\Facade\PdfOptimizer;
use Mostafaznv\PdfOptimizer\Enums\ColorConversionStrategy;
use Mostafaznv\PdfOptimizer\Enums\PdfSettings;

class PdfCompressionService
{
    public function compressAndUpload(UploadedFile $file, string $disk = 'public', string $directory = 'books'): string
    {
        // Log to see if this is being called
        \Log::info('PdfCompressionService::compressAndUpload called', [
            'file' => $file->getClientOriginalName(),
            'disk' => $disk,
            'directory' => $directory
        ]);
        
        try {
            // Set limits for large files
            set_time_limit(120); // 2 minutes
            ini_set('memory_limit', '512M');
            
            // Generate file names
            $tempFileName = 'temp_' . time() . '_' . $file->getClientOriginalName();
            $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '_compressed.pdf';
            
            // Store temp file locally first
            $tempPath = $file->storeAs('temp', $tempFileName, 'local');
            
            // Compress PDF with faster settings
            $result = PdfOptimizer::fromDisk('local')
                ->open($tempPath)
                ->toDisk($disk)
                ->settings(PdfSettings::EBOOK) // Faster compression
                ->colorConversionStrategy(ColorConversionStrategy::DEVICE_INDEPENDENT_COLOR)
                ->colorImageResolution(150) // Lower resolution = faster
                ->grayImageResolution(150)
                ->optimize($directory . '/' . $originalFileName);
            
            // Make file public
            Storage::disk($disk)->setVisibility($directory . '/' . $originalFileName, 'public');
            
            // Clean up temp file
            Storage::disk('local')->delete($tempPath);
            
            \Log::info('PdfCompressionService::compressAndUpload completed', ['path' => $directory . '/' . $originalFileName]);
            return $directory . '/' . $originalFileName;
            
        } catch (\Exception $e) {
            \Log::error('PDF compression failed, uploading original', ['error' => $e->getMessage()]);
            
            // If compression fails, upload original
            $originalName = $file->getClientOriginalName();
            $path = $file->storeAs($directory, $originalName, $disk);
            return $path;
        }
    }
    
    public function compressAndUploadWithQueue(UploadedFile $file, string $disk = 'public', string $directory = 'books'): array
    {
        $originalFileName = $file->getClientOriginalName();
        
        // Queue kullanarak optimize et
        $result = PdfOptimizer::fromUploadedFile($file)
            ->toDisk($disk)
            ->settings(PdfSettings::PRINTER) // Daha hızlı sıkıştırma
            ->colorConversionStrategy(ColorConversionStrategy::DEVICE_INDEPENDENT_COLOR)
            ->colorImageResolution(200)
            ->grayImageResolution(200)
            ->onQueue() // Queue'ya gönder
            ->optimize($directory . '/' . $originalFileName);
        
        return [
            'status' => $result->status,
            'message' => $result->message,
            'path' => $directory . '/' . $originalFileName,
            'original_size' => $file->getSize()
        ];
    }
}