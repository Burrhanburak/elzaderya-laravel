<?php

namespace App\Filament\Resources\Poems\Pages;

use App\Filament\Resources\Poems\PoemResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePoem extends CreateRecord
{
    protected static string $resource = PoemResource::class;
    
    public function mount(): void
    {
        parent::mount();
        
        // En baştan timeout'ları artır
        set_time_limit(0); // Unlimited
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', 600);
        ini_set('memory_limit', '512M');
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Execution time'ı tekrar artır
        set_time_limit(0); // Unlimited
        ini_set('max_execution_time', 0);
        
        return $data;
    }
    
    protected function beforeCreate(): void
    {
        // Create işleminden hemen önce de artır
        set_time_limit(0);
        ini_set('max_execution_time', 0);
    }
}
