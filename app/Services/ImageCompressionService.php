<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageCompressionService
{
    public function compressAndUpload(UploadedFile $file, string $disk = 'public', string $directory = 'books/covers'): string
    {
        // Log to see if this is being called
        \Log::info('ImageCompressionService::compressAndUpload called', [
            'file' => $file->getClientOriginalName(),
            'disk' => $disk,
            'directory' => $directory
        ]);
        
        try {
            // Set limits for image processing
            set_time_limit(60); // 1 minute
            ini_set('memory_limit', '256M');
            
            // Generate optimized filename
            $originalName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            $baseName = pathinfo($originalName, PATHINFO_FILENAME);
            $optimizedName = $baseName . '_optimized.' . ($extension === 'png' ? 'png' : 'jpg');
            
            // Create temp directory if not exists
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            // Save temp file
            $tempPath = $tempDir . '/' . time() . '_' . $originalName;
            $fileContent = $file->get();
            file_put_contents($tempPath, $fileContent);
            
            // Use ImageManager to optimize
            $manager = new ImageManager(new Driver());
            $image = $manager->read($tempPath);
            
            // Resize if too large (max 800x1000 for book covers)
            $maxWidth = 800;
            $maxHeight = 1000;
            
            if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
                $image->scaleDown($maxWidth, $maxHeight);
            }
            
            // Save optimized image
            $optimizedPath = $tempDir . '/optimized_' . time() . '.jpg';
            $image->toJpeg(85)->save($optimizedPath); // 85% quality
            
            // Upload to storage
            $finalPath = $directory . '/' . $optimizedName;
            Storage::disk($disk)->put($finalPath, file_get_contents($optimizedPath));
            Storage::disk($disk)->setVisibility($finalPath, 'public');
            
            // Clean up temp files
            if (file_exists($tempPath)) unlink($tempPath);
            if (file_exists($optimizedPath)) unlink($optimizedPath);
            
            \Log::info('ImageCompressionService::compressAndUpload completed', ['path' => $finalPath]);
            return $finalPath;
            
        } catch (\Exception $e) {
            \Log::error('Image compression failed, uploading original', ['error' => $e->getMessage()]);
            
            // If compression fails, upload original
            $originalName = $file->getClientOriginalName();
            $path = $file->storeAs($directory, $originalName, $disk);
            return $path;
        }
        
        // Geçici dosya adı oluştur (sadece güvenli karakterler)
        $originalFileName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $tempFileName = 'temp_' . time() . '_' . uniqid() . '.' . $extension;
        
        // Temp klasörünün var olduğundan emin ol
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Dosyayı doğrudan temp klasörüne kopyala
        $fullTempPath = $tempDir . '/' . $tempFileName;
        
        // Livewire TemporaryUploadedFile ile çalışmak için stream kullan
        try {
            // Önce dosyayı okumayı dene
            $fileContent = $file->get();
            
            if (empty($fileContent)) {
                throw new \Exception("Dosya içeriği boş");
            }
        } catch (\Exception $e) {
            // Alternatif yöntem: stream kullan
            $stream = $file->readStream();
            if (!$stream) {
                throw new \Exception("Dosya stream'i okunamadı: " . $e->getMessage());
            }
            
            $fileContent = stream_get_contents($stream);
            fclose($stream);
            
            if ($fileContent === false || empty($fileContent)) {
                throw new \Exception("Stream'den dosya içeriği okunamadı");
            }
        }
        
        $written = file_put_contents($fullTempPath, $fileContent);
        if ($written === false) {
            throw new \Exception("Geçici dosya oluşturulamadı: {$fullTempPath}");
        }
        
        // Dosyanın var olduğunu ve okunabilir olduğunu kontrol et
        if (!file_exists($fullTempPath)) {
            throw new \Exception("Geçici dosya oluşturulamadı: {$fullTempPath}");
        }
        
        // Dosya boyutunu kontrol et
        if (filesize($fullTempPath) === 0) {
            throw new \Exception("Dosya boş: {$fullTempPath}");
        }
        
        // ImageManager oluştur
        $manager = new ImageManager(new Driver());
        
        try {
            // Resmi yükle ve optimize et
            $image = $manager->read($fullTempPath);
        } catch (\Exception $e) {
            // Eğer Intervention Image okuyamazsa, basit sıkıştırma dene
            \Log::warning("Intervention Image başarısız, basit sıkıştırma deneniyor: " . $e->getMessage());
            
            try {
                // Basit resim sıkıştırma (PHP GD kullanarak)
                $s3Path = $this->simpleImageCompression($fullTempPath, $disk, $directory, $originalFileName);
                
                // Geçici dosyayı temizle
                if (file_exists($fullTempPath)) {
                    unlink($fullTempPath);
                }
                
                return $s3Path;
            } catch (\Exception $e2) {
                // Eğer basit sıkıştırma da başarısızsa, dosyayı olduğu gibi yükle
                \Log::warning("Basit sıkıştırma da başarısız, orijinal dosya yükleniyor: " . $e2->getMessage());
                
                // Dosyayı S3'e yükle
                $s3Path = $directory . '/' . $originalFileName;
                Storage::disk($disk)->put($s3Path, file_get_contents($fullTempPath));
                Storage::disk($disk)->setVisibility($s3Path, 'public');
                
                // Geçici dosyayı temizle
                if (file_exists($fullTempPath)) {
                    unlink($fullTempPath);
                }
                
                return $s3Path;
            }
        }
        
        // Resim boyutlarını kontrol et ve gerekirse yeniden boyutlandır (daha küçük boyutlar)
        $maxWidth = 800; // Maksimum genişlik (küçültüldü)
        $maxHeight = 1000; // Maksimum yükseklik (küçültüldü)
        
        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->scaleDown($maxWidth, $maxHeight);
        }
        
        // Kalite ayarları (daha düşük kalite = daha hızlı)
        $quality = 75; // %75 kalite (hız için düşürüldü)
        
        // Optimize edilmiş resmi geçici olarak kaydet
        $optimizedPath = storage_path('app/temp/optimized_' . $tempFileName);
        $image->toJpeg($quality)->save($optimizedPath);
        
        // S3'e yükle
        $s3Path = $directory . '/' . $originalFileName;
        Storage::disk($disk)->put($s3Path, file_get_contents($optimizedPath));
        
        // Dosyayı public yap
        Storage::disk($disk)->setVisibility($s3Path, 'public');
        
        // Geçici dosyaları temizle
        if (file_exists($fullTempPath)) {
            unlink($fullTempPath);
        }
        Storage::disk('local')->delete('temp/optimized_' . $tempFileName);
        
        return $s3Path;
    }
    
    private function simpleImageCompression(string $filePath, string $disk, string $directory, string $originalFileName): string
    {
        // Dosya tipini kontrol et
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            throw new \Exception("Geçersiz resim dosyası");
        }
        
        $mimeType = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        // Maksimum boyutları belirle (hız için küçültüldü)
        $maxWidth = 800;
        $maxHeight = 1000;
        
        // Yeni boyutları hesapla
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }
        
        // Kaynak resmi yükle
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($filePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($filePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($filePath);
                break;
            default:
                throw new \Exception("Desteklenmeyen resim formatı: {$mimeType}");
        }
        
        if (!$sourceImage) {
            throw new \Exception("Resim yüklenemedi");
        }
        
        // Yeni resim oluştur
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // PNG ve GIF için şeffaflığı koru
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resmi yeniden boyutlandır
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Optimize edilmiş resmi geçici olarak kaydet
        $optimizedPath = storage_path('app/temp/simple_optimized_' . time() . '_' . $originalFileName);
        $optimizedPath = str_replace($originalFileName, 'optimized_' . $originalFileName, $optimizedPath);
        
        // JPEG olarak kaydet (hız için kalite düşürüldü)
        $success = imagejpeg($newImage, $optimizedPath, 75);
        
        // Belleği temizle
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        if (!$success) {
            throw new \Exception("Optimize edilmiş resim kaydedilemedi");
        }
        
        // S3'e yükle
        $s3Path = $directory . '/' . $originalFileName;
        Storage::disk($disk)->put($s3Path, file_get_contents($optimizedPath));
        Storage::disk($disk)->setVisibility($s3Path, 'public');
        
        // Geçici dosyayı temizle
        unlink($optimizedPath);
        
        return $s3Path;
    }
    
    public function compressAndUploadWithQueue(UploadedFile $file, string $disk = 'public', string $directory = 'books/covers'): array
    {
        $originalFileName = $file->getClientOriginalName();
        $originalSize = $file->getSize();
        
        try {
            $path = $this->compressAndUpload($file, $disk, $directory);
            
            // Sıkıştırılmış dosya boyutunu al
            $compressedSize = Storage::disk($disk)->size($path);
            $compressionRatio = round((($originalSize - $compressedSize) / $originalSize) * 100, 2);
            
            return [
                'status' => 'success',
                'message' => 'Resim başarıyla sıkıştırıldı ve yüklendi',
                'path' => $path,
                'original_size' => $originalSize,
                'compressed_size' => $compressedSize,
                'compression_ratio' => $compressionRatio . '%'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Resim sıkıştırma hatası: ' . $e->getMessage(),
                'path' => null,
                'original_size' => $originalSize,
                'compressed_size' => 0,
                'compression_ratio' => '0%'
            ];
        }
    }
}
