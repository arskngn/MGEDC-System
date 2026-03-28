<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $logs = NotificationLog::query()
            ->with(['customer', 'user'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('notifications.index', ['logs' => $logs]);
    }
}
