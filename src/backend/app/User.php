<?php

namespace App;

use App\Transformers\UserTransformer;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'users';

    public const VERIFIED_USER = '1';
    public const UNVERIFIED_USER = '0';

    public const ADMIN_USER = 'true';
    public const REGULAR_USER = 'false';

    public $transformer = UserTransformer::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'verified', 'verification_token', 'admin'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'verification_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isVerified()
    {
        return $this->verified == self::VERIFIED_USER;
    }

    public function isAdmin()
    {
        return $this->admin == self::ADMIN_USER;
    }

    public static function generateVerificationCode()
    {
        return Str::random(40);
    }

    public function setNameAttribute($name)
    {
        $this->attributes['name'] = strtolower($name);
    }

    public function getNameAttribute($name)
    {
        return ucwords($name);
    }

    public function setEmailAttribute($email)
    {
        $this->attributes['email'] = strtolower($email);
    }
}
