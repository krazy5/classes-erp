<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $status = $request->string('status')->trim();

        $feedback = Feedback::query()
            ->with(['author', 'responder'])
            ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId))
            ->when($status->isNotEmpty(), fn ($builder) => $builder->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.feedback.index', [
            'feedback' => $feedback,
            'filters' => [
                'status' => $status->value(),
            ],
        ]);
    }

    public function update(Request $request, Feedback $feedback): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;

        if ($tenantId && $feedback->tenant_id !== $tenantId) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved'],
            'response' => ['nullable', 'string'],
        ]);

        $feedback->fill([
            'status' => $data['status'],
            'response' => $data['response'] ?? null,
            'responded_by' => $request->user()->id,
            'responded_at' => now(),
        ])->save();

        return redirect()->route('admin.feedback.index')
            ->with('status', 'Feedback updated.');
    }

    public function destroy(Request $request, Feedback $feedback): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;

        if ($tenantId && $feedback->tenant_id !== $tenantId) {
            abort(403);
        }

        $feedback->delete();

        return redirect()->route('admin.feedback.index')
            ->with('status', 'Feedback removed.');
    }
}
