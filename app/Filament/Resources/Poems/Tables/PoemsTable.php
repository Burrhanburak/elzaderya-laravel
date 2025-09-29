<?php

namespace App\Filament\Resources\Poems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PoemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Kapak')
                    ->disk('s3')
                    ->visibility('public')
                    ->url(fn ($record) => $record->cover_image ? Storage::disk('s3')->url($record->cover_image) : null)
                    ->rounded()
                    ->size(60),
                
                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('preview_pdf')
                    ->label('Ön İzleme PDF')
                    ->formatStateUsing(function ($state) {
                        if ($state) {
                            $fileName = basename($state);
                            return "📄 " . Str::limit($fileName, 20);
                        }
                        return 'Yok';
                    })
                    ->url(fn ($record) => $record->preview_pdf ? Storage::disk('public')->url($record->preview_pdf) : null)
                    ->openUrlInNewTab()
                    ->color('primary'),

                TextColumn::make('description')
                    ->label('Açıklama')
                    ->limit(30)
                    ->words(3)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
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

                TextColumn::make('price')
                    ->label('Fiyat')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('polar_product_id')
                    ->label('Polar Product ID'),
                // TextColumn::make('polar_price_id')
                //     ->label('Paddle Price ID'),
                
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
