<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Traits\LocalizedDiffForHumansTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, SoftDeletes, Notifiable, LocalizedDiffForHumansTrait;

    public function messages(){
        $this->hasMany(Message::class);
    }

    public function getImageUrl($avatar)
    {
      return "app/public/images/profile/{$avatar}";
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'email', 'password', 'avatar',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $guarded = ['id', 'deleted_at', 'created_at', 'updated_at',];
}
