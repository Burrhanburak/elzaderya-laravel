<?php

namespace App\Filament\Resources\Blogs\Schemas;

use App\Models\Category;
use App\Services\ImageCompressionService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class BlogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Blog Bilgileri')
                    ->schema([
                        TextInput::make('title')
                            ->label('Başlık')
                            ->required()
                            ->maxLength(255),
                        
                        FileUpload::make('cover_image')
                            ->label('Kapak Görseli')
                            ->image()
                            ->disk('s3')
                            ->maxSize(10240) // 10 MB
                            ->directory('blogs/covers')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                            ->storeFileNamesIn('cover_image_filename'),
                           
                        
                        DateTimePicker::make('published_at')
                            ->label('Yayın Tarihi')
                            ->seconds(false),
                        
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
                        
                        Select::make('categories')
                            ->label('Kategoriler')
                            ->multiple()
                            ->relationship('categories', 'name_tr', function ($query, $get) {
                                return $query;
                            })
                            ->getOptionLabelFromRecordUsing(function ($record, $get) {
                                $language = $get('language') ?? 'tr';
                                return $record->{'name_' . $language} ?? $record->name_tr;
                            })
                            ->preload()
                            ->searchable()
                            ->required(),
                    ]),

               


                
                Section::make('İçerik')
                    ->schema([
                        RichEditor::make('content')
                            ->label('Blog İçeriği')
                            ->required()
                            ->toolbarButtons([
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ]),
                    ]),
            ]);
    }
}
