<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Comment extends Model
{
    use SoftDeletes, Notifiable;

    public function user(){
        $this->belongsTo(User::class);
    }

    protected $fillable = [
        'comment',
    ];

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at','owner','message',];

    protected $dates = ['deleted_at',];
}
