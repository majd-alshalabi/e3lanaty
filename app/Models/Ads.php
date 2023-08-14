<?php

namespace App\Models;

use App\Models\View;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ads extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'ads_type',
        'extra_description',
        'price',
        'type',
        'stared',
        'admin',
        'status',
        'priorty',
        'link',
        'user_id',
    ];

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function favorits(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function adsDescriptions(): HasMany
    {
        return $this->hasMany(AdsDescription::class);
    }

    public function advantages(): HasMany
    {
        return $this->hasMany(Advantage::class);
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(View::class);
    }
}
