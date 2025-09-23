<?php

namespace App\Filament\Resources\Poems\Pages;

use App\Filament\Resources\Poems\PoemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPoem extends EditRecord
{
    protected static string $resource = PoemResource::class;
    
    public function mount($record = null): void
    {
        parent::mount($record);
        
        // En baştan timeout'ları artır
        set_time_limit(0); // Unlimited
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', 600);
        ini_set('memory_limit', '512M');
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Execution time'ı tekrar artır
        set_time_limit(0); // Unlimited
        ini_set('max_execution_time', 0);
        
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
