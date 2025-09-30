<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Support\StudentDocumentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentDocumentController extends Controller
{
    public function create(Request $request, Student $student): View
    {
        $this->assertTenant($request, $student);

        $student->loadMissing('media');

        return view('admin.students.documents', [
            'student' => $student,
            'documentTypes' => StudentDocumentService::options(),
            'onboarding' => $request->boolean('onboarding'),
        ]);
    }

    public function store(Request $request, Student $student): RedirectResponse
    {
        $this->assertTenant($request, $student);

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

        $routeParams = ['student' => $student];

        if ($request->boolean('onboarding')) {
            $routeParams['onboarding'] = 1;
        }

        return redirect()
            ->route('admin.students.documents', $routeParams)
            ->with('status', $uploaded > 1 ? "$uploaded documents uploaded successfully." : 'Document uploaded successfully.');
    }

    public function destroy(Request $request, Student $student, Media $media): RedirectResponse
    {
        $this->assertTenant($request, $student);
        $this->ensureMediaBelongsToStudent($media, $student);

        $media->delete();

        return back()->with('status', 'Document removed.');
    }

    public function download(Request $request, Student $student, Media $media): StreamedResponse
    {
        $this->assertTenant($request, $student);
        $this->ensureMediaBelongsToStudent($media, $student);

        return response()->download($media->getPath(), $media->file_name);
    }

    protected function assertTenant(Request $request, Student $student): void
    {
        $tenantId = $request->user()->tenant_id;

        if ($tenantId && $student->tenant_id !== $tenantId) {
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

