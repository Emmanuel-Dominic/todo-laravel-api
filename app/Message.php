<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\LocalizedDiffForHumansTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Message extends Model
{
    use SoftDeletes, Notifiable, LocalizedDiffForHumansTrait;

    public function user(){
        $this->belongsTo(User::class);
    }

    protected $fillable = [
        'message', 'status',
    ];

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at','owner', 'comment_on'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

}
