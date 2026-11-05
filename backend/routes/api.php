<?php

use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\DailyNoteController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ScratchNoteController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\WorkLogController;
use Illuminate\Support\Facades\Route;

// Single-user, tanpa auth di Fase 1. Bearer token (scope admin) untuk Yuna ditambahkan di Fase 3.
Route::apiResource('projects', ProjectController::class)->except('show');
Route::apiResource('todos', TodoController::class)->except('show');
Route::apiResource('folders', FolderController::class)->except('show');
Route::apiResource('tags', TagController::class)->only(['index', 'destroy']);
Route::apiResource('scratch-notes', ScratchNoteController::class)->except('show')
    ->parameters(['scratch-notes' => 'scratchNote']);

Route::apiResource('calendar-events', CalendarEventController::class)->except('show')
    ->parameters(['calendar-events' => 'calendarEvent']);

Route::get('work-logs/active', [WorkLogController::class, 'active']);
Route::post('work-logs/start', [WorkLogController::class, 'start']);
Route::apiResource('work-logs', WorkLogController::class)->except('show')
    ->parameters(['work-logs' => 'workLog']);

Route::get('daily-note', [DailyNoteController::class, 'show']);
Route::put('daily-note', [DailyNoteController::class, 'upsert']);
