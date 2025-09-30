<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\Student;
use Illuminate\Support\Collection;

class AnnouncementFeed
{
    /**
     * Fetch announcements relevant to a student, including class and global broadcasts.
     */
    public static function forStudent(Student $student, ?int $tenantId = null, int $limit = 5): Collection
    {
        return Announcement::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where(function ($query) use ($student) {
                $query->whereHas('students', fn ($relation) => $relation->where('student_id', $student->id))
                    ->orWhereHas('classGroups', fn ($relation) => $relation->where('class_group_id', $student->class_group_id))
                    ->orWhere(function ($broadcast) {
                        $broadcast->doesntHave('classGroups')->doesntHave('students');
                    });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
