<?php

namespace App\Filament\Resources\Poems\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class PoemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Şiir Bilgileri')
                    ->schema([
                        TextInput::make('title')
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
                        
                        Select::make('language')
                            ->label('Dil')
                            ->required()
                            ->options([
                                'tr' => 'Türkçe',
                                'en' => 'English',
                                'ru' => 'Русский',
                                'az' => 'Azərbaycan',
                            ])
                            ->default('tr')
                            ->reactive(),
                        
                        TextInput::make('price')
                            ->label('Fiyat')
                            ->required()
                            ->numeric()
                            ->prefix(fn ($get) => match ($get('language')) {
                                'tr' => '₺',
                                'en' => '$',
                                'ru' => '₽',
                                'az' => '₼',
                                default => '$',
                            })
                            ->step(0.01)
                            ->minValue(0)
                            ->reactive(),
                    ]),
                
                Section::make('Dosyalar')
                    ->schema([
                        FileUpload::make('cover_image')
                            ->label('Kapak Görseli')
                            ->image()
                            ->disk('s3')
                            ->maxSize(10240) // 10 MB
                            ->directory('poems/covers')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                            ->storeFileNamesIn('cover_image_filename'),
                         
                        FileUpload::make('preview_pdf')
                            ->label('Ön İzleme PDF')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(51200) // 50 MB
                            ->disk('s3')
                            ->directory('poems/previews')
                            ->visibility('public')
                            ->storeFileNamesIn('preview_pdf_filename'),
                        
                        FileUpload::make('full_pdf')
                            ->label('Tam PDF')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(102400) // 100 MB
                            ->disk('s3')
                            ->directory('poems/full')
                            ->visibility('public')
                            ->storeFileNamesIn('full_pdf_filename')
                    ]),


                    Section::make('Polar Bilgileri')
                    ->schema([
                        TextInput::make('currency')
                            ->label('Para Birimi')
                            ->default('USD')
                            ->disabled()
                            ->helperText('Polar sadece USD destekler, fiyat tarayıcı diline göre gösterilir'),
                        TextInput::make('lemon_variant_id')
                            ->label('Lemon Variant ID')
                            ->nullable(),
                        // TextInput::make('polar_price_id')
                        //     ->label('Polar Price ID'),
                    ]),
            ]);
    }
}
