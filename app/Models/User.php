<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'deleted',
        'about_me',
        'location',
        'image',
        'rate',
        'blocked',
        'type_of_account',
        'admin',
        'point',
        'deleted',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];


    public static function search($query)
    {
        return self::where('name', 'LIKE', "%$query%")
            ->get();
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ads::class);
    }

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

    public function feedBacks(): HasMany
    {
        return $this->hasMany(FeedBack::class);
    }
    public function userSetting(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }
    public function follows(): HasMany
    {
        return $this->hasMany(Follow::class);
    }
    public function views(): HasMany
    {
        return $this->hasMany(View::class);
    }
}
