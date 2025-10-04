<?php

namespace App\Livewire\Student;

use App\Models\Attendance;
use App\Models\QrCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class MarkAttendance extends Component
{
    public bool $showModal = false;
    public ?string $statusMessage = null;
    public string $statusLevel = 'info';
    public string $elementId;

    public function mount(): void
    {
        $this->elementId = 'qr-reader-' . Str::uuid()->toString();
    }

    public function open(): void
    {
        $this->authorizeStudent();
        $this->resetFeedback();
        $this->showModal = true;
        $this->dispatch('qr-modal-opened', id: $this->elementId);
    }

    public function close(): void
    {
        $this->showModal = false;
        $this->dispatch('qr-modal-closed');
    }

    #[On('student-attendance:token')]
    public function handleToken(string $token): void
    {
        $this->verifyToken($token);
    }

    public function verifyToken(string $token): void
    {
        $token = trim($token);

        if ($token === '') {
            $this->setFeedback('Invalid or expired QR code.', 'error');
            $this->dispatch('qr-scan-error');
            return;
        }

        $user = $this->authorizeStudent();
        $student = $user->studentProfile;

        if (!$student) {
            $this->setFeedback('Student profile not found.', 'error');
            return;
        }

        $tenantId = $user->tenant_id;
        $today = now()->toDateString();

        $code = QrCode::query()
            ->where('token', $token)
            ->forDate($today)
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->first();

        if (!$code || ($code->expires_at && $code->expires_at->isPast())) {
            $this->setFeedback('Invalid or expired QR code.', 'error');
            $this->dispatch('qr-scan-error');
            return;
        }

        Attendance::updateOrCreate(
            [
                'student_id' => $student->id,
                'date' => $today,
            ],
            [
                'tenant_id' => $tenantId,
                'present' => true,
                'qr_code_id' => $code->id,
                'marked_at' => now(),
                'marked_via' => 'qr',
            ]
        );

        $code->update(['last_used_at' => now()]);

        $this->setFeedback('Attendance marked successfully!', 'success');
        $this->dispatch('qr-scan-success');
        $this->dispatch('attendance-updated');
    }

    public function render()
    {
        return view('livewire.student.mark-attendance');
    }

    protected function authorizeStudent()
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('student'), 403);

        return $user;
    }

    protected function resetFeedback(): void
    {
        $this->statusMessage = null;
        $this->statusLevel = 'info';
    }

    protected function setFeedback(string $message, string $level): void
    {
        $this->statusMessage = $message;
        $this->statusLevel = $level;
    }
}
