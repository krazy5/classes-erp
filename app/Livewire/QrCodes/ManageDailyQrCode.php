<?php

namespace App\Livewire\QrCodes;

use App\Models\QrCode as QrCodeModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

class ManageDailyQrCode extends Component
{
    public ?QrCodeModel $code = null;
    public ?string $statusMessage = null;

    public function mount(): void
    {
        $this->authorizeUser();
        $this->loadCode();
    }

    public function generate(): void
    {
        $user = Auth::user();
        $this->authorizeUser($user);

        $tenantId = $user->tenant_id;
        $today = now()->toDateString();
        $token = $this->generateUniqueToken();

        $this->code = QrCodeModel::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'issued_for_date' => $today,
            ],
            [
                'issued_by_id' => $user->id,
                'token' => $token,
                'expires_at' => now()->endOfDay(),
            ]
        )->fresh();

        $this->statusMessage = 'QR code generated for today.';
        Log::info('QR code generated', ['id' => $this->code->id ?? null, 'tenant_id' => $tenantId]);
    }

    public function refreshCode(): void
    {
        $this->loadCode();
        $this->statusMessage = null;
    }

    public function getSvgDataUriProperty(): ?string
    {
        if (!$this->code) {
            return null;
        }

        $svg = QrCodeGenerator::format('svg')
            ->size(280)
            ->margin(1)
            ->generate($this->code->token);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function render()
    {
        return view('livewire.qr-codes.manage-daily-qr-code');
    }

    protected function loadCode(): void
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $this->code = QrCodeModel::query()
            ->where('tenant_id', $tenantId)
            ->forDate(now())
            ->latest('id')
            ->first();
    }

    protected function authorizeUser($user = null): void
    {
        $user ??= Auth::user();

        abort_unless($user && $user->hasAnyRole(['admin', 'manager', 'teacher']), 403);
    }

    protected function generateUniqueToken(): string
    {
        do {
            $token = Str::random(48);
        } while (QrCodeModel::where('token', $token)->exists());

        return $token;
    }
}
