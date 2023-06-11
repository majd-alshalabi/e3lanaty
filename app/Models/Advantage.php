<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Advantage extends Model
{
    use HasFactory;

    protected $fillable = [
        'advantage',
    ];


    public function ads(): BelongsTo
    {
        return $this->belongsTo(Ads::class, 'ads_id', 'id');
    }
}
