<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loot extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $table = 'loot';

    protected $fillable = [
        'id',
        'player',
        'date',
        'time',
        'item',
        'item_id',
        'item_string',
        'response',
        'votes',
        'class',
        'instance',
        'boss',
        'difficulty_id',
        'map_id',
        'group_size',
        'gear1',
        'gear2',
        'response_id',
        'is_award_reason',
        'sub_type',
        'equip_loc',
        'note',
        'owner',
    ];

    /**
     * Set the date attribute.
     *
     * @param  string  $value
     * @return void
     */
    public function setDateAttribute($value)
    {
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{2}$/', $value)) {
            // Parse the date with a specified format
            $date = Carbon::createFromFormat('d/m/y', $value);

            // If the parsed date is before a certain threshold, you might want to add 100 years
            if ($date->year < 2000) {
                $date->addYears(100);
            }

            // Set the formatted date to the model's attribute
            $this->attributes['date'] = $date->format('Y-m-d');
        } else {
            // If the date is not in the expected format or is null, set it to null
            $this->attributes['date'] = null;
        }
    }
}
