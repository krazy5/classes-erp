<?php

namespace App\Http\Controllers\Parents;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Support\StudentDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentDocumentController extends Controller
{
    public function store(Request $request, Student $student): RedirectResponse
    {
        $this->ensureGuardianAccess($request, $student);

        $documentTypes = array_keys(StudentDocumentService::options());
        $allowedTypes = array_merge($documentTypes, ['other']);

        $data = $request->validate([
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['file', 'max:20480'],
            'document_type' => ['nullable', 'string', Rule::in($allowedTypes)],
            'document_label' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $documentType = $data['document_type'] ?? null;
        $documentLabel = $data['document_label'] ?? null;

        if ($documentType === 'other' && blank($documentLabel)) {
            throw ValidationException::withMessages([
                'document_label' => 'Please provide a label when choosing Other as the document type.',
            ]);
        }

        $files = collect($request->file('documents', []))
            ->filter()
            ->all();

        if (empty($files)) {
            throw ValidationException::withMessages([
                'documents' => 'Please select at least one document to upload.',
            ]);
        }

        $uploaded = StudentDocumentService::store($student, $files, $request->user(), [
            'type' => $documentType,
            'label' => $documentLabel,
            'description' => $data['description'] ?? null,
        ]);

        return redirect()
            ->route('parent.dashboard', ['student_user_id' => optional($student->user)->getKey()])
            ->with('status', $uploaded > 1 ? "$uploaded documents uploaded successfully." : 'Document uploaded successfully.');
    }

    public function download(Request $request, Student $student, Media $media): StreamedResponse
    {
        $this->ensureGuardianAccess($request, $student);
        $this->ensureMediaBelongsToStudent($media, $student);

        return response()->download($media->getPath(), $media->file_name);
    }

    protected function ensureGuardianAccess(Request $request, Student $student): void
    {
        $user = $request->user();

        abort_unless($user && $user->hasRole('parent'), 403);

        if ($user->tenant_id && $student->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $studentUserId = optional($student->user)->getKey();

        if (! $studentUserId) {
            abort(403);
        }

        $hasAccess = $user->students()->whereKey($studentUserId)->exists();

        if (! $hasAccess) {
            abort(403);
        }
    }

    protected function ensureMediaBelongsToStudent(Media $media, Student $student): void
    {
        if ($media->model_type !== Student::class || (int) $media->model_id !== (int) $student->getKey()) {
            abort(404);
        }
    }
}
