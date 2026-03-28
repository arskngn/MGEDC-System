<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'subject',
        'email_body',
        'sms_body',
        'is_email_enabled',
        'is_sms_enabled',
        'shortcodes',
        'email_sent_from_name',
        'email_sent_from_email',
        'sms_sent_from',
        'push_title',
        'push_body',
        'is_push_enabled',
    ];

    protected $casts = [
        'shortcodes' => 'array',
        'is_email_enabled' => 'boolean',
        'is_sms_enabled' => 'boolean',
        'is_push_enabled' => 'boolean',
    ];
}
