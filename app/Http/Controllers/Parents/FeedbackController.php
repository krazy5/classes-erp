<?php

namespace App\Http\Controllers\Parents;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'category' => ['nullable', 'string', 'max:100'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string'],
        ]);

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
