<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Traits\HasTenant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class CashierResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Staff Kasir';
    
    protected static ?string $pluralLabel = 'Kasir';
    
    protected static ?string $slug = 'cashiers';

    // IMPORTANT: Only show cashiers, not the tenant themselves
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', 'cashier');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Kasir')
                    ->required(),
                TextInput::make('email')
                    ->label('Email Login')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->required(fn ($record) => $record === null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->label('Password'),
                Select::make('branch_id')
                    ->label('Ditugaskan di Cabang')
                    ->relationship('branch', 'name', fn (Builder $query) => $query->where('user_id', auth()->id()))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kasir')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\CashierResource\Pages\ListCashiers::route('/'),
            'create' => \App\Filament\Resources\CashierResource\Pages\CreateCashier::route('/create'),
            'edit' => \App\Filament\Resources\CashierResource\Pages\EditCashier::route('/{record}/edit'),
        ];
    }
}
