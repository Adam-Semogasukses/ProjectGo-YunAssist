<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    /** GET /calendar-events?from=YYYY-MM-DD&to=YYYY-MM-DD (keduanya opsional, inklusif). */
    public function index(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d',
        ]);

        return CalendarEvent::query()
            ->when($request->filled('from'), fn ($q) => $q->where('event_date', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($q) => $q->where('event_date', '<=', $request->query('to')))
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->integer('project_id')))
            ->orderBy('event_date')->orderBy('id')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date_format:Y-m-d',
            'event_type' => 'sometimes|in:deadline,event,target',
            'project_id' => 'nullable|exists:projects,id',
        ]);
        return response()->json(CalendarEvent::create($data), 201);
    }

    public function update(Request $request, CalendarEvent $calendarEvent)
    {
        $calendarEvent->update($request->validate([
            'title' => 'sometimes|string|max:255',
            'event_date' => 'sometimes|date_format:Y-m-d',
            'event_type' => 'sometimes|in:deadline,event,target',
            'project_id' => 'sometimes|nullable|exists:projects,id',
        ]));
        return $calendarEvent->fresh();
    }

    public function destroy(CalendarEvent $calendarEvent)
    {
        $calendarEvent->delete();
        return response()->noContent();
    }
}
