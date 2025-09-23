<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchasable_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'book' => 'success',
                        'poem' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('product_name')
                    ->label('Product')
                    ->getStateUsing(function ($record) {
                        $product = $record->getProduct();
                        return $product ? $product->title : 'Product not found';
                    }),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('TRY')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                    ]),
                BadgeColumn::make('email_sent')
                    ->label('Email Sent')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => true,
                        'heroicon-o-x-circle' => false,
                    ]),
                TextColumn::make('paddle_transaction_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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
            ]);
    }
}
