<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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

    protected static function booted()
    {
        static::addGlobalScope('date_greater_than', function (Builder $builder) {
            $builder->where('date', '>', Date::createFromFormat('Y-m-d', '2023-07-12'));
        });
    }
}
