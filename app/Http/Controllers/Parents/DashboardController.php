<?php
namespace App\Http\Controllers\Parents;

use App\Http\Controllers\Controller;
use App\Models\FeeRecord;
use App\Support\StudentDashboardData;
use App\Support\AnnouncementFeed;
use App\Support\StudentDocumentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        abort_unless($user->can('dashboard.view.parent'), 403);

        $children = $user->students()
            ->with(['studentProfile.classGroup'])
            ->get()
            ->sortBy('name')
            ->values();

        $selectedChildId = (int) $request->input('student_user_id');
        $selectedChild = $children->firstWhere('id', $selectedChildId) ?? $children->first();

        $tenantId = $user->tenant_id;

        $studentProfile = optional($selectedChild)->studentProfile;

        $studentData = $studentProfile
            ? StudentDashboardData::make($studentProfile, $tenantId)
            : [
                'classGroup' => null,
                'timetableByDay' => collect(),
                'attendanceSummary' => [
                    'total' => 0,
                    'present' => 0,
                    'absent' => 0,
                    'percentage' => null,
                ],
                'recentAttendance' => collect(),
            ];

        $feeRecords = collect();

        $announcements = collect();

        $documents = collect();
        $documentTypes = StudentDocumentService::options();

        if ($studentProfile) {
            $studentProfile->loadMissing('media');

            $feeRecords = FeeRecord::with([
                'installments' => fn ($query) => $query->orderBy('sequence')->with('fines'),
                'feeStructure',
                'discounts',
            ])
                ->where('student_id', $studentProfile->id)
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->orderByDesc('created_at')
                ->get();

            $announcements = AnnouncementFeed::forStudent($studentProfile, $tenantId);
            $documents = $studentProfile->getMedia('documents')->sortByDesc('created_at');
        }

        return view('parent.dashboard', array_merge($studentData, [
            'guardian' => $user,
            'children' => $children,
            'selectedChild' => $selectedChild,
            'selectedStudent' => $studentProfile,
            'feeRecords' => $feeRecords,
            'announcements' => $announcements,
            'documents' => $documents,
            'documentTypes' => $documentTypes,
        ]));
    }
}






