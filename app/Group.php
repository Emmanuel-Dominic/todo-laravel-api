<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\LocalizedDiffForHumansTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Group extends Model
{
    use SoftDeletes, Notifiable, LocalizedDiffForHumansTrait;

    protected $fillable = [
        'name', 'purpose'
    ];

    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at', 'owner',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
