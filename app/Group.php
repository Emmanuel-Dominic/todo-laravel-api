<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Group extends Model
{
    use SoftDeletes, Notifiable;

    protected $fillable = [
        'name', 'purpose'
    ];

    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at', 'owner',
    ];

    protected $dates = ['deleted_at'];
}
