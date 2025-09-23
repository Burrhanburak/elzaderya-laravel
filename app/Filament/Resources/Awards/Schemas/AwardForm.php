<?php

namespace App\Filament\Resources\Awards\Schemas;

use App\Services\ImageCompressionService;
use App\Services\PdfCompressionService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AwardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ödül Bilgileri')
                    ->schema([
                        TextInput::make('name')
                            ->label('Ödül Adı')
                            ->required()
                            ->maxLength(255),
                        
                        Textarea::make('description')
                            ->label('Açıklama')
                            ->required()
                            ->rows(4),
                        
                        FileUpload::make('file_url')
                            ->label('Ödül Dosyası')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                            ->maxSize(20480) // 20 MB
                            ->disk('s3')
                            ->image()
                            ->directory('awards')
                            ->visibility('public')
                            ->storeFileNamesIn('file_url_filename')
                            ->saveUploadedFileUsing(function ($file) {
                                $mimeType = $file->getMimeType();
                                
                                if ($mimeType === 'application/pdf') {
                                    $compressionService = new PdfCompressionService();
                                } else {
                                    $compressionService = new ImageCompressionService();
                                }
                                
                                return $compressionService->compressAndUpload($file, 's3', 'awards');
                            }),
                         
                        
                        Select::make('language')
                            ->label('Dil')
                            ->required()
                            ->options([
                                'tr' => 'Türkçe',
                                'en' => 'English',
                                'ru' => 'Русский',
                                'az' => 'Azərbaycan',
                            ])
                            ->default('tr'),
                    ]),
            ]);
    }
}
