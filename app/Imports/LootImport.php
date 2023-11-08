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
        // Assume the first row of the collection is the header.
        $headers = $collection->first()->toArray();

        // Loop through the rest of the collection, starting from the second row.
        foreach ($collection->slice(1) as $row) {
            // Combine the headers with the row items to create an associative array.
            $rowData = array_combine($headers, $row->toArray());

            // You may need to define which columns are used to determine "uniqueness" for firstOrCreate.
            // For example, if 'id' is unique:
            $uniqueKey = ['id' => $rowData['id']];

            // Use firstOrCreate to either fetch the existing record or create a new one.
            Loot::firstOrCreate($uniqueKey, $rowData);
        }
    }
}
