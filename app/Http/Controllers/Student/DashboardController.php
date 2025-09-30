<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Support\StudentDashboardData;
use App\Support\AnnouncementFeed;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        abort_unless($user->can('dashboard.view.student'), 403);

        $student = $user->studentProfile;

        if (!$student) {
            abort(404, 'Student profile not found.');
        }

        $tenantId = $user->tenant_id;

        $data = StudentDashboardData::make($student, $tenantId);

        $announcements = AnnouncementFeed::forStudent($student, $tenantId);

        return view('student.dashboard', array_merge([
            'student' => $student,
            'announcements' => $announcements,
        ], $data));
    }
}
