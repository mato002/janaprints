<?php

namespace App\Http\Controllers\Ess;

use App\Enums\EmployeeDocumentCategory;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Ess\Concerns\ResolvesEmployee;
use App\Models\Hr\EmployeeDocument;
use App\Support\Ess\EssAuditService;
use App\Support\Hr\EmployeeDocumentService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EssDocumentController extends Controller
{
    use ResolvesEmployee;

    /**
     * @var list<string>
     */
    protected array $restrictedCategories = [
        EmployeeDocumentCategory::WarningLetter->value,
        EmployeeDocumentCategory::PerformanceReview->value,
        EmployeeDocumentCategory::ExitDocument->value,
    ];

    public function download(
        EmployeeDocument $document,
        EmployeeDocumentService $documents,
        EssAuditService $audit,
    ): StreamedResponse {
        $employee = $this->essEmployee();
        $user = $this->essUser();

        abort_unless($user->can('ess.documents.download'), 403);
        $this->assertOwnEmployee($document, $employee);
        abort_unless((int) $document->company_id === (int) $employee->company_id, 403);
        abort_unless($document->is_active, 403);
        abort_unless(! in_array($document->category->value, $this->restrictedCategories, true), 403);

        $audit->logDocumentDownloaded($employee, $user, $document->id, $document->title);

        return $documents->download($document);
    }
}
