<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Message extends Model
{
    use SoftDeletes, Notifiable;

    public function user(){
        $this->belongsTo(User::class);
    }

    protected $fillable = [
        'message',
    ];

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at','owner',];

    protected $dates = ['deleted_at',];

}
