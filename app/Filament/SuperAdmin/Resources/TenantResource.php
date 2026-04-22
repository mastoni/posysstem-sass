<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class TenantResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Penyewa (Tenants)';
    
    protected static ?string $pluralLabel = 'Penyewa';

    public static function form(Form $form): Form
    {
        return $form
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(fn ($record) => $record === null)
                    ->dehydrated(fn ($state) => filled($state)),
                Select::make('role')
                    ->options([
                        'superadmin' => 'Super Admin',
                        'tenant' => 'Tenant (Penyewa)',
                    ])
                    ->required(),
                TextInput::make('store_name'),
                TextInput::make('store_type'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'superadmin' => 'danger',
                        'tenant' => 'success',
                    }),
                TextColumn::make('store_name')
                    ->label('Nama Toko'),
                TextColumn::make('internet_package')
                    ->label('Paket Sembok'),
            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\SuperAdmin\Resources\TenantResource\Pages\ListTenants::route('/'),
        ];
    }
}
