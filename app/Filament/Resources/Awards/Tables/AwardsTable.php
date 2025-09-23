<?php

namespace App\Filament\Resources\Awards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AwardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('file_url')
                    ->label('Görsel')
                    ->disk('s3')
                    ->size(60)
                    ->square(),
                
                TextColumn::make('name')
                    ->label('Ödül Adı')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('description')
                    ->label('Açıklama')
                    ->limit(50)
                    ->wrap(),
                
                TextColumn::make('language')
                    ->label('Dil')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tr' => 'success',
                        'en' => 'info',
                        'ru' => 'warning',
                        'az' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tr' => 'Türkçe',
                        'en' => 'English',
                        'ru' => 'Русский',
                        'az' => 'Azərbaycan',
                        default => $state,
                    }),
                
                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('language')
                    ->label('Dil')
                    ->options([
                        'tr' => 'Türkçe',
                        'en' => 'English',
                        'ru' => 'Русский',
                        'az' => 'Azərbaycan',
                    ]),
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
