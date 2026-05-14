<?php

namespace App\Filament\Superadmin\Resources\BusinessResource\Pages;

use App\Filament\Superadmin\Resources\BusinessResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBusiness extends ViewRecord
{
    protected static string $resource = BusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $owner = $this->record->owner;

        if ($owner) {
            $data['surname']    = $owner->surname;
            $data['first_name'] = $owner->first_name;
            $data['last_name']  = $owner->last_name;
            $data['username']   = $owner->username;
            $data['email']      = $owner->email;
        }

        return $data;
    }
}
