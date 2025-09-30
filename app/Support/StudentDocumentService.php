<?php

namespace App\Support;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class StudentDocumentService
{
    /**
     * Commonly used document types for student records.
     */
    public const DOCUMENT_TYPES = [
        'aadhaar' => 'Aadhaar Card',
        'marksheet' => 'Marksheet',
        'past_result' => 'Past Result',
        'leaving_certificate' => 'Leaving Certificate',
        'transfer_certificate' => 'Transfer Certificate',
        'migration_certificate' => 'Migration Certificate',
        'birth_certificate' => 'Birth Certificate',
        'id_proof' => 'ID Proof',
    ];

    /**
     * Return the configured document type options.
     */
    public static function options(): array
    {
        return self::DOCUMENT_TYPES;
    }

    /**
     * Resolve a friendly label for a document upload.
     */
    public static function resolveLabel(?string $type, ?string $customLabel = null): ?string
    {
        $label = trim((string) $customLabel);

        if ($label !== '') {
            return $label;
        }

        if ($type && isset(self::DOCUMENT_TYPES[$type])) {
            return self::DOCUMENT_TYPES[$type];
        }

        return null;
    }

    /**
     * Store uploaded documents against the given student.
     *
     * @param  array<int, UploadedFile>  $files
     */
    public static function store(Student $student, array $files, User $uploader, array $context = []): int
    {
        $type = $context['type'] ?? null;
        $label = static::resolveLabel($type, $context['label'] ?? null);
        $description = $context['description'] ?? null;
        $uploaded = 0;
        $roleName = $uploader->getRoleNames()->first();

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $student->addMedia($file)
                ->usingName($label ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                ->withCustomProperties([
                    'type' => $type,
                    'type_label' => $type && isset(self::DOCUMENT_TYPES[$type]) ? self::DOCUMENT_TYPES[$type] : null,
                    'label' => $label,
                    'description' => $description,
                    'uploaded_by' => $uploader->id,
                    'uploaded_by_name' => $uploader->name,
                    'uploaded_by_role' => $roleName,
                    'original_name' => $file->getClientOriginalName(),
                ])
                ->toMediaCollection('documents');

            $uploaded++;
        }

        return $uploaded;
    }
}

