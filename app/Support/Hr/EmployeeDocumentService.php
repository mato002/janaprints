<?php

namespace App\Support\Hr;

use App\Enums\EmployeeDocumentCategory;
use App\Models\Employee;
use App\Models\Hr\EmployeeDocument;
use App\Models\Hr\EmployeeDocumentVersion;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeDocumentService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = EmployeeDocument::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['employee', 'uploadedBy'])
            ->where('is_active', true);

        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['expiry'])) {
            match ($filters['expiry']) {
                'expiring' => $query->expiringSoon((int) ($filters['reminder_days'] ?? 30)),
                'expired' => $query->expired(),
                default => null,
            };
        }

        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $query->where(function ($inner) use ($search) {
                $inner->where('title', 'like', $search)
                    ->orWhereHas('employee', function ($employee) use ($search) {
                        $employee->where('first_name', 'like', $search)
                            ->orWhere('last_name', 'like', $search)
                            ->orWhere('employee_number', 'like', $search);
                    });
            });
        }

        return $query->orderByDesc('updated_at')->paginate($perPage)->withQueryString();
    }

    /**
     * @return array<string, mixed>
     */
    public function formData(int $companyId): array
    {
        return [
            'employees' => Employee::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('first_name')
                ->get(),
            'categories' => EmployeeDocumentCategory::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardStats(int $companyId): array
    {
        $base = EmployeeDocument::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->where('is_active', true);

        return [
            'total_documents' => (clone $base)->count(),
            'employees_with_documents' => (clone $base)->distinct('employee_id')->count('employee_id'),
            'expiring_soon' => (clone $base)->expiringSoon(30)->count(),
            'expired' => (clone $base)->expired()->count(),
        ];
    }

    /**
     * @return Collection<int, EmployeeDocument>
     */
    public function renewalAlerts(int $companyId, int $days = 30): Collection
    {
        return EmployeeDocument::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['employee'])
            ->where(function ($query) use ($days) {
                $query->expiringSoon($days)->orWhere(fn ($inner) => $inner->expired());
            })
            ->orderBy('expires_at')
            ->limit(20)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, array $data, UploadedFile $file, User $user): EmployeeDocument
    {
        $employee = Employee::query()
            ->where('company_id', $companyId)
            ->whereKey($data['employee_id'])
            ->firstOrFail();

        return DB::transaction(function () use ($companyId, $data, $file, $user, $employee) {
            $document = EmployeeDocument::query()->create([
                'company_id' => $companyId,
                'employee_id' => $employee->id,
                'category' => $data['category'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'expires_at' => $data['expires_at'] ?? null,
                'renewal_reminder_days' => $data['renewal_reminder_days'] ?? 30,
                'current_version' => 0,
            ]);

            $this->storeVersion($document, $file, $user, $data['notes'] ?? null);

            return $document->fresh(['employee', 'versions.uploadedBy']);
        });
    }

    public function uploadVersion(EmployeeDocument $document, UploadedFile $file, User $user, ?string $notes = null): EmployeeDocumentVersion
    {
        if (! $document->is_active) {
            throw ValidationException::withMessages([
                'file' => __('This document is archived and cannot receive new versions.'),
            ]);
        }

        return DB::transaction(function () use ($document, $file, $user, $notes) {
            return $this->storeVersion($document, $file, $user, $notes);
        });
    }

    public function download(EmployeeDocument $document, ?EmployeeDocumentVersion $version = null): StreamedResponse
    {
        $version ??= $document->currentVersion();

        if (! $version) {
            abort(404);
        }

        abort_unless(Storage::disk('local')->exists($version->path), 404);

        return Storage::disk('local')->download($version->path, $version->original_name);
    }

    public function delete(EmployeeDocument $document): void
    {
        DB::transaction(function () use ($document) {
            foreach ($document->versions as $version) {
                $this->deleteVersionFile($version);
            }

            $document->versions()->delete();
            $document->delete();
        });
    }

    protected function storeVersion(
        EmployeeDocument $document,
        UploadedFile $file,
        User $user,
        ?string $notes = null,
    ): EmployeeDocumentVersion {
        $versionNumber = $document->current_version + 1;
        $directory = "hr-documents/{$document->company_id}/{$document->employee_id}/{$document->id}";
        $path = $file->store($directory, 'local');

        $version = EmployeeDocumentVersion::query()->create([
            'employee_document_id' => $document->id,
            'version_number' => $versionNumber,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by_user_id' => $user->id,
            'notes' => $notes,
        ]);

        $document->update([
            'current_version' => $versionNumber,
            'uploaded_by_user_id' => $user->id,
        ]);

        return $version;
    }

    protected function deleteVersionFile(EmployeeDocumentVersion $version): void
    {
        if ($version->path && Storage::disk('local')->exists($version->path)) {
            Storage::disk('local')->delete($version->path);
        }
    }
}
