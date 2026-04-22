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
                TextInput::make('phone')
                    ->label('WhatsApp/Telepon')
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
                    ->default('tenant')
                    ->required(),
                TextInput::make('store_name')
                    ->label('Nama Toko/Bisnis'),
                TextInput::make('store_type')
                    ->label('Jenis Usaha'),
                TextInput::make('store_address')
                    ->label('Alamat Bisnis'),
                TextInput::make('billing_customer_id')
                    ->label('Billing Sembok ID')
                    ->placeholder('Kosongkan jika penyewa mandiri'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Pemilik')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('WA')
                    ->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'superadmin' => 'danger',
                        'tenant' => 'success',
                        default => 'gray'
                    }),
                TextColumn::make('store_name')
                    ->label('Bisnis')
                    ->searchable(),
                TextColumn::make('billing_customer_id')
                    ->label('Sembok ID')
                    ->badge()
                    ->color('info')
                    ->placeholder('Independent'),
                TextColumn::make('is_setup_completed')
                    ->label('Status Setup')
                    ->formatStateUsing(fn ($state) => $state ? '✅ Lengkap' : '⏳ Belum')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
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
