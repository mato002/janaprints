<?php

namespace App\Support\Hr;

use App\Models\Employee;
use App\Models\Hr\PayrollPayslip;
use App\Models\Hr\PayrollRun;
use App\Models\User;
use App\Support\Hr\PayrollConfidentialityService;
use App\Support\Communications\Email\CorporateMailDispatcher;
use App\Support\Communications\Email\EmailAttachmentMaterializer;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class EmployeeEmailService
{
    public function __construct(
        protected CorporateMailDispatcher $mail,
    ) {}

    /**
     * @param  list<int|string>  $employeeIds
     * @return Collection<int, Employee>
     */
    public function resolveRecipients(int $companyId, array $employeeIds = [], bool $allActive = false): Collection
    {
        $query = EmployeeRosterQuery::query($companyId, ['active' => $allActive ? true : null])
            ->whereNotNull('email')
            ->where('email', '!=', '');

        if ($allActive) {
            // active filter already applied via roster query
        } elseif ($employeeIds !== []) {
            $query->whereIn('id', array_map('intval', $employeeIds));
        } else {
            return collect();
        }

        return $query->orderBy('first_name')->orderBy('last_name')->get();
    }

    /**
     * @return array{queued: int, skipped: int, message_ids: list<int>}
     */
    public function sendMessage(
        int $companyId,
        int $userId,
        Collection $employees,
        string $subject,
        string $body,
    ): array {
        if ($employees->isEmpty()) {
            throw ValidationException::withMessages([
                'employees' => __('Select at least one employee with an email address.'),
            ]);
        }

        $queued = 0;
        $skipped = 0;
        $messageIds = [];

        foreach ($employees as $employee) {
            if (! filled($employee->email)) {
                $skipped++;

                continue;
            }

            $message = $this->mail->dispatch([
                'company_id' => $companyId,
                'branch_id' => $employee->branch_id,
                'user_id' => $userId,
                'to' => [[
                    'email' => (string) $employee->email,
                    'name' => (string) $employee->full_name,
                ]],
                'subject' => $subject,
                'body' => $this->formatBody($body, $employee),
                'sender_purpose' => 'employee_message',
                'metadata' => [
                    'module' => 'hr',
                    'entity_type' => 'employee',
                    'entity_id' => $employee->id,
                ],
            ]);

            if ($message !== null) {
                $queued++;
                $messageIds[] = (int) $message->id;
            } else {
                $skipped++;
            }
        }

        return [
            'queued' => $queued,
            'skipped' => $skipped,
            'message_ids' => $messageIds,
        ];
    }

    public function sendPayslip(PayrollPayslip $payslip, ?User $actor = null): bool
    {
        $payslip->loadMissing(['employee', 'payrollRun']);
        $employee = $payslip->employee;
        $email = $employee?->email;

        if (! filled($email)) {
            return false;
        }

        $attachment = EmailAttachmentMaterializer::payslipStub($payslip);
        $actorId = (int) ($actor?->id ?? auth()->id() ?? 1);
        $period = $payslip->payrollRun?->period_end?->format('F Y') ?? '';

        $message = $this->mail->dispatch([
            'company_id' => (int) $payslip->company_id,
            'branch_id' => $employee?->branch_id,
            'user_id' => $actorId,
            'to' => [[
                'email' => (string) $email,
                'name' => (string) ($employee?->full_name ?? ''),
            ]],
            'subject' => __('Payslip for :period', ['period' => $period]),
            'body' => $this->payslipBody($payslip, $employee),
            'sender_purpose' => 'payslip',
            'attachments' => [$attachment],
            'metadata' => app(PayrollConfidentialityService::class)->markConfidentialMetadata([
                'module' => 'hr',
                'entity_type' => 'payroll_payslip',
                'entity_id' => $payslip->id,
                'document_number' => (string) ($payslip->reference ?? ''),
            ], 'payslip'),
        ]);

        return $message !== null;
    }

    /**
     * @return array{queued: int, skipped: int}
     */
    public function sendPayslipsForRun(PayrollRun $run, User $actor): array
    {
        $run->loadMissing(['payslips.employee']);

        $queued = 0;
        $skipped = 0;

        foreach ($run->payslips as $payslip) {
            if ($this->sendPayslip($payslip, $actor)) {
                $queued++;
            } else {
                $skipped++;
            }
        }

        return [
            'queued' => $queued,
            'skipped' => $skipped,
        ];
    }

    protected function formatBody(string $body, Employee $employee): string
    {
        $personalized = str_replace(
            ['{{name}}', '{{employee_number}}'],
            [e($employee->full_name), e($employee->employee_number)],
            $body,
        );

        if (! str_contains($personalized, '<')) {
            return '<p>'.nl2br(e($personalized)).'</p>';
        }

        return $personalized;
    }

    protected function payslipBody(PayrollPayslip $payslip, ?Employee $employee): string
    {
        $period = $payslip->payrollRun?->period_end?->format('F Y') ?? '';

        return '<p>'.e(__('Dear :name,', ['name' => $employee?->full_name ?? __('Colleague')])).'</p>'
            .'<p>'.e(__('Please find your payslip for :period attached.', ['period' => $period])).'</p>'
            .'<p>'.e(__('Salary details are included in the attached PDF only.')).'</p>';
    }
}
