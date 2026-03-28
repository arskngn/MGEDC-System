<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'shortcode',
        'status',
        'help_text',
    ];

    protected $casts = [
        'shortcode' => 'array',
        'status' => 'boolean',
    ];
}
