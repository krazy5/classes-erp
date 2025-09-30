<?php

namespace App\Support;

use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class UpcomingBirthdays
{
    /**
     * Retrieve upcoming student birthdays for a tenant.
     */
    public static function forTenant(
        ?int $tenantId,
        int $daysAhead = 14,
        ?array $classGroupIds = null,
        int $limit = 6
    ): Collection {
        $query = Student::query()
            ->with('classGroup')
            ->whereNotNull('dob');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        if ($classGroupIds) {
            $query->whereIn('class_group_id', $classGroupIds);
        }

        $students = $query->get();

        $today = Carbon::today();

        return $students
            ->map(function (Student $student) use ($today) {
                if (!$student->dob) {
                    return null;
                }

                $dob = $student->dob instanceof Carbon
                    ? $student->dob->copy()
                    : Carbon::parse($student->dob);

                $nextBirthday = $dob->copy()->setYear($today->year);

                if ($nextBirthday->isBefore($today)) {
                    $nextBirthday->addYear();
                }

                $daysUntil = $today->diffInDays($nextBirthday);

                $student->setAttribute('next_birthday', $nextBirthday);
                $student->setAttribute('days_until_birthday', $daysUntil);

                return $student;
            })
            ->filter(fn ($student) => $student && $student->days_until_birthday <= $daysAhead)
            ->sortBy(fn ($student) => [$student->next_birthday->timestamp, $student->name])
            ->values()
            ->take($limit);
    }
}
