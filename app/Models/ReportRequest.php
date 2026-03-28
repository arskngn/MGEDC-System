<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportRequest extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'message',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
