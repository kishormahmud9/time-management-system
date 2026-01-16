<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;


/**
 * @method bool hasRole(string|array $roles)
 * @method \Illuminate\Database\Eloquent\Collection|mixed assignRole(...$roles)
 * @method bool hasAnyRole(...$roles)
 * @method bool hasAllRoles(...$roles)
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|User where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|User first()
 * @method static \Illuminate\Database\Eloquent\Builder|User create(array $attributes = [])
 */
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'phone',
        'gender',
        'business_id',
        'image',
        'signature',
        'marital_status',
        'status',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
    // public function company()
    // {
    //     return $this->belongsTo(Business::class);
    // }

    public function userDetails()
    {
        return $this->hasOne(UserDetail::class);
    }
}
