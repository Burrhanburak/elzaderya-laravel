<?php

namespace App\Filament\Resources\Certificates\Schemas;

use App\Services\ImageCompressionService;
use App\Services\PdfCompressionService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class CertificateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sertifika Bilgileri')
                    ->schema([

                        TextInput::make('name')
                        ->label('Sertifika Adı')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                            if (($get('slug') ?? '') !== Str::slug($old)) return;
                            $set('slug', Str::slug($state));
                        }),
                     

                        TextInput::make('slug') ,
                        
                        Textarea::make('description')
                            ->label('Açıklama')
                            ->required()
                            ->rows(4),
                        
                        FileUpload::make('file_url')
                            ->label('Sertifika Dosyası')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                            ->maxSize(204800) // 200 MB
                            ->disk('s3')
                            ->image()
                            ->directory('certificates')
                            ->visibility('public')
                            ->storeFileNamesIn('file_url_filename')
                            ->saveUploadedFileUsing(function ($file) {
                                $mimeType = $file->getMimeType();
                                
                                if ($mimeType === 'application/pdf') {
                                    $compressionService = new PdfCompressionService();
                                } else {
                                    $compressionService = new ImageCompressionService();
                                }
                                
                                return $compressionService->compressAndUpload($file, 's3', 'certificates');
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
