<?php

namespace App\Filament\Superadmin\Resources\BusinessResource\Pages;

use App\Filament\Superadmin\Resources\BusinessResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusiness extends EditRecord
{
    protected static string $resource = BusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $owner = $this->record->owner;

        if ($owner) {
            $ownerFields = ['surname', 'first_name', 'last_name', 'username', 'email'];
            $ownerData   = [];

            foreach ($ownerFields as $field) {
                if (isset($data[$field])) {
                    $ownerData[$field] = $data[$field];
                }
                unset($data[$field]);
            }

            if (!empty($ownerData)) {
                $owner->update($ownerData);
            }
        }

        // Strip subscription fields — not Business columns
        foreach (['package_id', 'paid_via', 'payment_transaction_id'] as $field) {
            unset($data[$field]);
        }

        return $data;
    }
}
