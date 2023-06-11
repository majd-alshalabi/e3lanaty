<?php

namespace App\Models;

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
        'extra_description',
        'price',
        'type',
        'status',
        'priorty',
        'link',
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

    public function advantages(): HasMany
    {
        return $this->hasMany(Advantage::class);
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
