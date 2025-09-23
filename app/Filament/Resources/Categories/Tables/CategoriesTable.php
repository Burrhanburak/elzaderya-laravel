<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_tr')
                    ->label('Kategori (TR)')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('name_en')
                    ->label('Kategori (EN)')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('name_ru')
                    ->label('Kategori (RU)')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('name_az')
                    ->label('Kategori (AZ)')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('color')
                    ->label('Renk')
                    ->badge()
                    ->color(fn (string $state): string => $state)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
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
                        default => $state,
                    }),
                
                TextColumn::make('blogs_count')
                    ->label('Blog Sayısı')
                    ->counts('blogs')
                    ->badge()
                    ->color('info'),
                
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