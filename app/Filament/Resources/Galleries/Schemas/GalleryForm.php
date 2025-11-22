<?php

namespace App\Filament\Resources\Galleries\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GalleryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Başlık')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Açıklama')
                    ->rows(4)
                    ->columnSpanFull(),

                FileUpload::make('images')
                    ->label('Resimler')
                    ->image()
                    ->multiple()
                    ->disk('s3')
                    ->directory('galleries/images')
                    ->reorderable()
                    ->maxSize(10240) // 10 MB
                    ->downloadable()
                   ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])

                     ->visibility('public')
                    ->openable()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '16:9',
                        '4:3',
                        '1:1',
                    ])
                    ->columnSpanFull()
                    ->helperText('Birden fazla resim yükleyebilirsiniz. Sürükleyerek sıralayabilirsiniz.'),

                TagsInput::make('tags')
                    ->label('Etiketler')
                    ->placeholder('Etiket ekle...')
                    ->helperText('Enter tuşuna basarak etiket ekleyin')
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->inline(false)
                    ->helperText('Galeri öğesini yayınlamak için aktif yapın'),
            ]);
    }
}
