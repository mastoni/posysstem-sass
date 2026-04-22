<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;

class OrderForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->components([
                TextInput::make('order_number')
                    ->required(),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('payment_method')
                    ->required()
                    ->default('cash'),
                TextInput::make('status')
                    ->required()
                    ->default('completed'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
