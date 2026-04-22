<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Forms\Form;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                
                // Tambahan Field Toko
                TextInput::make('store_name')
                    ->label('Nama Toko')
                    ->required()
                    ->placeholder('Contoh: Toko Berkah Jaya'),
                
                Select::make('store_type')
                    ->label('Jenis Bisnis')
                    ->options([
                        'Retail' => 'Toko Kelontong / Retail',
                        'Cafe' => 'Cafe / Restoran',
                        'Jasa' => 'Jasa / Servis',
                        'Fashion' => 'Fashion & Pakaian',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->required(),
                
                Textarea::make('store_address')
                    ->label('Alamat Lengkap')
                    ->required()
                    ->placeholder('Jl. Raya No. 123...'),
                
                TextInput::make('store_phone')
                    ->label('No. WhatsApp Toko')
                    ->tel()
                    ->required()
                    ->placeholder('08123456789'),
            ]);
    }

    protected function handleRegistration(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Pastikan role diset sebagai tenant dan is_setup_completed = true
        $data['role'] = 'tenant';
        $data['is_setup_completed'] = true;
        
        return parent::handleRegistration($data);
    }
}
