<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->disabled(),
                Select::make('purchasable_type')
                    ->label('Product Type')
                    ->options([
                        'book' => 'Book',
                        'poem' => 'Poem',
                    ])
                    ->required()
                    ->disabled(),
                TextInput::make('purchasable_id')
                    ->label('Product ID')
                    ->numeric()
                    ->required()
                    ->disabled(),
                TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->suffix('TRY')
                    ->required()
                    ->disabled(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),
                TextInput::make('paddle_transaction_id')
                    ->label('Paddle Transaction ID')
                    ->disabled(),
                Select::make('email_sent')
                    ->label('Email Sent')
                    ->options([
                        true => 'Yes',
                        false => 'No',
                    ])
                    ->boolean()
                    ->disabled(),
                TextInput::make('email_sent_at')
                    ->label('Email Sent At')
                    ->disabled(),
            ]);
    }
}
