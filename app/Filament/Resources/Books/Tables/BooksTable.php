<?php

namespace App\Filament\Resources\Books\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Joaopaulolndev\FilamentPdfViewer\Forms\Components\PdfViewerField;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class BooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Kapak')
                    ->square()
                    ->disk('s3')
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
                    ->color('primary')
                   ->size(20),
                   
                   TextColumn::make('full_pdf')
                    ->label('Tam PDF')
                    ->formatStateUsing(function ($state) {
                        if ($state) {
                            $fileName = basename($state);
                            return "📄 " . Str::limit($fileName, 20);
                        }
                        return 'Yok';
                    })
                    ->url(fn ($record) => $record->full_pdf ? Storage::disk('public')->url($record->full_pdf) : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->size(20),

                
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
                
                TextColumn::make('price')
                    ->label('Fiyat')
                    ->money('TRY')
                    ->sortable(),
                
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

                    TextInputColumn::make('lemon_variant_id')
                    ->label('Lemon Variant ID'),
                
                
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
