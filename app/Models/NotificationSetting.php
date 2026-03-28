<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'email_sent_from_name',
        'email_sent_from_email',
        'email_body',
        'email_method',
        'email_config',
        'sms_sent_from',
        'sms_body',
        'sms_method',
        'sms_config',
    ];

    protected $casts = [
        'email_config' => 'array',
        'sms_config' => 'array',
    ];
}
