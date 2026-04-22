<?php

namespace App\Filament\Resources;

use App\Models\Branch;
use App\Traits\HasTenant;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    
    protected static ?string $navigationLabel = 'Cabang Toko';
    
    protected static ?string $pluralLabel = 'Cabang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Cabang')
                    ->required(),
                TextInput::make('address')
                    ->label('Alamat Cabang'),
                TextInput::make('phone')
                    ->label('Telepon Cabang'),
                Toggle::make('is_main')
                    ->label('Cabang Utama'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Cabang')
                    ->searchable(),
                TextColumn::make('address')
                    ->label('Alamat'),
                IconColumn::make('is_main')
                    ->label('Utama')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\BranchResource\Pages\ListBranches::route('/'),
            'create' => \App\Filament\Resources\BranchResource\Pages\CreateBranch::route('/create'),
            'edit' => \App\Filament\Resources\BranchResource\Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
