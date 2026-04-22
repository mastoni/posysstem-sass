<?php

namespace App\Filament\Resources\CashierResource\Pages;

use App\Filament\Resources\CashierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCashier extends CreateRecord
{
    protected static string $resource = CashierResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'cashier';
        $data['owner_id'] = auth()->id();
        
        return $data;
    }
}
