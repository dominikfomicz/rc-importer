<?php

namespace App\Filament\Resources\LootResource\Pages;

use App\Filament\Resources\LootResource;
use App\Imports\LootImport;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLoots extends ListRecords
{
    protected static string $resource = LootResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color("primary")
                ->slideOver()
                ->color("primary")
                ->use(LootImport::class),
        ];
    }
}
