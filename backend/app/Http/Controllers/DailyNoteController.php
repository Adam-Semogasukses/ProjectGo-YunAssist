<?php

namespace App\Http\Controllers;

use App\Models\DailyNote;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DailyNoteController extends Controller
{
    public function show(Request $request)
    {
        $date = $request->query('date', Carbon::now('Asia/Jakarta')->toDateString());
        return DailyNote::firstOrNew(['date' => $date]);
    }

    public function upsert(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'focus_note' => 'nullable|string',
        ]);
        return DailyNote::updateOrCreate(['date' => $data['date']], ['focus_note' => $data['focus_note'] ?? '']);
    }
}
