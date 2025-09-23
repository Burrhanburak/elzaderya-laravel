<?php

namespace App\Filament\Resources\Blogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Kapak')
                    ->disk('s3')
                    ->square()
                    ->size(60),
                
                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('category')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('content')
                    ->label('İçerik')
                    // ->html()
                    ->limit(10)
                    ->words(1),
                
                TextColumn::make('published_at')
                    ->label('Yayın Tarihi')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('d.m.Y H:i') : 'Taslak'),
                
                TextColumn::make('language')
                    ->label('Dil')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tr' => 'Türkçe',
                        'en' => 'English',
                        'ru' => 'Русский',
                        'az' => 'Azərbaycan',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tr' => 'danger',
                        'en' => 'success',
                        'ru' => 'warning',
                        'az' => 'info',
                        default => 'gray',
                    }),
                
                TextColumn::make('created_at')
                    ->label('Oluşturulma Tarihi')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('Güncellenme Tarihi')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
