<?php

namespace App\Filament\Resources\Certificates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CertificatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('file_url')
                    ->label('Dosya')
                    ->square()
                    ->disk('s3')
                    ->size(60),

                
                TextColumn::make('name')
                    ->label('Sertifika Adı')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('description')
                    ->label('Açıklama')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    }),
                
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
