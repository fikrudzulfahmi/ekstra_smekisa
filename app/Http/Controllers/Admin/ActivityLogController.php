<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $activities = Activity::with('causer')
            ->latest()
            ->paginate(15)
            ->through(function ($activity) {
                return [
                    'id' => $activity->id,
                    'log_name' => $activity->log_name,
                    'description' => $activity->description,
                    'subject_type' => class_basename($activity->subject_type),
                    'subject_id' => $activity->subject_id,
                    'causer_name' => $activity->causer ? $activity->causer->name : 'System',
                    'properties' => $activity->properties,
                    'created_at' => $activity->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return inertia('Admin/ActivityLog/Index', [
            'activities' => $activities
        ]);
    }

    public function clear()
    {
        Activity::truncate();

        return back()->with('success', 'Riwayat log aktivitas berhasil dibersihkan.');
    }
}
