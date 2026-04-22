<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Models\PpobProduct;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use App\Services\PpobService;
use Filament\Notifications\Notification;

class PpobProductResource extends Resource
{
    protected static ?string $model = PpobProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    
    protected static ?string $navigationLabel = 'Produk PPOB';
    
    protected static ?string $pluralLabel = 'Produk PPOB';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code')->required(),
                TextInput::make('name')->required(),
                TextInput::make('category')->required(),
                TextInput::make('brand')->required(),
                TextInput::make('price_buy')->numeric()->required(),
                TextInput::make('price_sell')->numeric()->required(),
                Toggle::make('is_active')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category')->badge()->sortable(),
                TextColumn::make('brand')->searchable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('code')->label('SKU'),
                TextColumn::make('price_buy')->money('IDR'),
                TextColumn::make('price_sell')->money('IDR'),
                ToggleColumn::make('is_active'),
            ])
            ->headerActions([
                Action::make('sync')
                    ->label('Sinkron Produk')
                    ->action(function () {
                        (new PpobService())->syncProducts();
                        Notification::make()->title('Produk PPOB berhasil disinkronkan')->success()->send();
                    })
                    ->color('info')
                    ->icon('heroicon-o-arrow-path'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\SuperAdmin\Resources\PpobProductResource\Pages\ListPpobProducts::route('/'),
        ];
    }
}
