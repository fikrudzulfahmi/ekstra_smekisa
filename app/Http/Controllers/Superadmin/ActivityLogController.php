<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $activities = Activity::with('causer')
            ->latest()
            ->paginate(20)
            ->through(function ($activity) {
                return [
                    'id'           => $activity->id,
                    'log_name'     => $activity->log_name,
                    'description'  => $activity->description,
                    'subject_type' => class_basename($activity->subject_type),
                    'subject_id'   => $activity->subject_id,
                    'causer_name'  => $activity->causer ? $activity->causer->name : 'System',
                    'causer_role'  => $activity->causer ? $activity->causer->role : null,
                    'properties'   => $activity->properties,
                    'created_at'   => $activity->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return inertia('Superadmin/ActivityLog/Index', [
            'activities' => $activities,
        ]);
    }

    public function clear()
    {
        Activity::truncate();
        return back()->with('success', 'Semua riwayat log aktivitas berhasil dibersihkan.');
    }
}
