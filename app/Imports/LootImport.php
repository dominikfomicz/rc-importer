<?php

namespace App\Imports;

use App\Models\Loot;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Str;

class LootImport implements ToCollection
{
    /**
     * Convert header names from camelCase (or any case) to snake_case.
     * Handles specific cases like 'difficultyID' to 'difficulty_id'.
     *
     * @param string $header
     * @return string
     */
    protected function convertToSnakeCase($header)
    {
        $exceptions = [
            'player'       => 'player',
            'date'         => 'date',
            'time'         => 'time',
            'id'           => 'id',
            'item'         => 'item',
            'itemID'       => 'item_id',
            'itemString'   => 'item_string',
            'response'     => 'response',
            'votes'        => 'votes',
            'class'        => 'class',
            'instance'     => 'instance',
            'boss'         => 'boss',
            'difficultyID' => 'difficulty_id',
            'mapID'        => 'map_id',
            'groupSize'    => 'group_size',
            'gear1'        => 'gear1',
            'gear2'        => 'gear2',
            'responseID'   => 'response_id',
            'isAwardReason' => 'is_award_reason',
            'subType'      => 'sub_type',
            'equipLoc'     => 'equip_loc',
            'note'         => 'note',
            'owner'        => 'owner',
        ];

        // Return the exception if it exists, otherwise convert to snake_case
        return $exceptions[$header] ?? Str::snake($header);
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $idsToDelete = $collection->slice(1)->pluck('id')->unique()->all();
        Loot::whereIn('id', $idsToDelete)->delete();

        // Convert headers to snake_case with exceptions
        $headers = $collection->first()->map(function ($header) {
            return $this->convertToSnakeCase($header);
        })->toArray();

        $rowsToInsert = [];

        foreach ($collection->slice(1) as $row) {
            $rowData = array_combine($headers, $row->toArray());

            // Convert dates for each row
            if (isset($rowData['date']) && $rowData['date']) {
                if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{2}$/', $rowData['date'])) {
                    // Parse the date with a specified format
                    $date = Carbon::createFromFormat('d/m/y', $rowData['date']);

                    // If the parsed date is before a certain threshold, you might want to add 100 years
                    if ($date->year < 2000) {
                        $date->addYears(100);
                    }

                    // Set the formatted date to the model's attribute
                    $rowData['date'] = $date->format('Y-m-d');
                } else {
                    // If the date is not in the expected format or is null, set it to null
                    $rowData['date'] = null;
                }
            }

            $rowsToInsert[] = $rowData;
        }

        // Now, instead of inserting one by one, we'll do a bulk insert.
        // Execute the insert operation in chunks to prevent memory issues
        $chunks = array_chunk($rowsToInsert, 500); // Adjust the chunk size if needed
        foreach ($chunks as $chunk) {
            Loot::insert($chunk);
        }
    }
}
