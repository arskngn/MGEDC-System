<?php

use App\Models\NotificationTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        NotificationTemplate::updateOrCreate(
            ['slug' => 'staff-onboarding'],
            [
                'name' => 'Staff Onboarding',
                'subject' => 'Welcome to {{site_name}} - Your Login Details',
                'email_body' => '<p>Hello {{fullname}},</p><p>Welcome to <strong>{{site_name}}</strong>! You have been added as a staff member.</p><p>Here are your login credentials:</p><ul><li><strong>Email:</strong> {{email}}</li><li><strong>Password:</strong> {{password}}</li></ul><p>You can login at: <a href="{{login_url}}">{{login_url}}</a></p><p>Best regards,<br>{{site_name}} Team</p>',
                'sms_body' => 'Welcome to {{site_name}}, {{fullname}}! Your login email is {{email}} and password is {{password}}. Login at: {{login_url}}',
                'is_email_enabled' => true,
                'is_sms_enabled' => false,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        NotificationTemplate::where('slug', 'staff-onboarding')->delete();
    }
};
