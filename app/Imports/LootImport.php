<?php

namespace App\Imports;

use App\Models\Loot;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class LootImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $headers = $collection->first()->toArray();

        $collection->slice(1)->chunk(10)->each(function ($chunk) use ($headers) {
            foreach ($chunk as $row) {
                $rowData = array_combine($headers, $row->toArray());
                $uniqueKey = ['id' => $rowData['id']];

                Loot::firstOrCreate($uniqueKey, $rowData);
            }
        });
    }
}
