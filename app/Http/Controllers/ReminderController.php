<?php
// PATH FILE: app/Http/Controllers/ReminderController.php

namespace App\Http\Controllers;

use App\Support\ReminderSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->query('filter');

        $summary = ReminderSummaryService::summaryForUser($user);
        $reminders = ReminderSummaryService::listForUser($user, $filter, 50);

        return view('reminders.index', compact('summary', 'reminders', 'filter'));
    }

    public function count()
    {
        return response()->json([
            'count' => ReminderSummaryService::countForUser(Auth::user()),
        ]);
    }
}
