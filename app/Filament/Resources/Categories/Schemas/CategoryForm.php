<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               
                Section::make('Kategori Bilgileri')
                ->description('Kategori bilgilerini giriniz.')
                ->icon('heroicon-o-tag')

                ->schema([
                    TextInput::make('name_tr')
                    ->label('Kategori Adı (Türkçe)')
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('name_en')
                    ->label('Kategori Adı (English)')
                    ->required()
                    ->maxLength(255),

                ])
                ->columns(2),

                Section::make('Kategori Bilgileri')
                ->description('Kategori bilgilerini giriniz.')
                ->icon('heroicon-o-tag')
                    ->schema([
                   
                        
                        
                        TextInput::make('name_ru')
                            ->label('Kategori Adı (Русский)')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('name_az')
                            ->label('Kategori Adı (Azərbaycan)')
                            ->required()
                            ->maxLength(255)

                    ])
                    ->columns(2),
                        

             

                Section::make('URL slug ve renk seçimi')
                ->description('URL slug ve renk seçimi yapınız.')
                ->icon('heroicon-o-tag')
                        ->schema([
                        TextInput::make('slug')
                        ->label('URL Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->rules(['alpha_dash']),
                    
                    Select::make('color')
                    ->label('Renk')
                    ->options([
                        'gray' => 'Gri',
                        'success' => 'Yeşil',
                        'info' => 'Mavi',
                        'warning' => 'Sarı',
                        'danger' => 'Kırmızı',
                        'purple' => 'Mor',
                        'emerald' => 'Zümrüt',
                        'blue' => 'Mavi',
                        'indigo' => 'İndigo',
                        'pink' => 'Pembe',
                        'violet' => 'Violet',
                        'orange' => 'Turuncu',
                        'green' => 'Yeşil',
                        'teal' => 'Teal',
                        'red' => 'Kırmızı',
                    ])
                    ->default('gray')
                    ->required(),
                    ])
                    ->columns(2),

            ]);
    }
}