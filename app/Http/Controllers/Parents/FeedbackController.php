<?php

namespace App\Http\Controllers\Parents;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'category' => ['nullable', 'string', 'max:100'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string'],
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id'),
            ],
        ]);

        $student = Student::query()
            ->when($user->tenant_id, fn ($query) => $query->where('tenant_id', $user->tenant_id))
            ->findOrFail($data['student_id']);

        $studentUserId = $student->user_id;
        $ownsStudent = $studentUserId
            ? $user->students()->whereKey($studentUserId)->exists()
            : false;

        abort_unless($ownsStudent, 403);

        Feedback::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'category' => $data['category'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
        ]);

        return redirect()->route('parent.dashboard')
            ->with('status', 'Thank you for your feedback. Our team will review it shortly.');
    }
}
