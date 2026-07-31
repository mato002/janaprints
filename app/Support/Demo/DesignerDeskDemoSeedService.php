<?php

namespace App\Support\Demo;

use App\Enums\ArtworkCommentType;
use App\Enums\ArtworkPriority;
use App\Enums\ArtworkRequestStatus;
use App\Models\Artwork\ArtworkComment;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DesignerDeskDemoSeedService
{
    public function run(?Command $command = null): void
    {
        $company = Company::query()->where('code', 'JANA')->first()
            ?? Company::query()->orderBy('id')->first();
        $branch = $company
            ? (Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->first()
                ?? Branch::query()->where('company_id', $company->id)->orderBy('id')->first())
            : null;

        if (! $company || ! $branch) {
            $command?->warn('Designer Desk demo seed skipped — company/branch missing.');

            return;
        }

        $sales = User::query()->where('email', 'sales@janaprints.local')->first()
            ?? User::query()->where('company_id', $company->id)->orderBy('id')->first();
        $designer = User::query()->where('email', 'designer@janaprints.local')->first();
        $admin = User::query()->where('email', 'admin@janaprints.local')->first();

        if (! $sales) {
            $command?->warn('Designer Desk demo seed skipped — no requester user found.');

            return;
        }

        $customers = Customer::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->orderBy('id')
            ->limit(5)
            ->get();

        if ($customers->isEmpty()) {
            $customers = collect([
                Customer::query()->create([
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'company_name' => 'Desk Demo Customer',
                    'customer_code' => 'DESK-DEMO-001',
                    'customer_type' => \App\Enums\CustomerType::Corporate,
                    'status' => \App\Enums\CustomerStatus::Active,
                ]),
            ]);
        }

        $marker = 'DESK-DEMO';
        ArtworkRequest::query()
            ->where('company_id', $company->id)
            ->where('request_number', 'like', $marker.'%')
            ->each(function (ArtworkRequest $request) {
                $request->versions()->delete();
                $request->comments()->delete();
                $request->files()->delete();
                $request->approvals()->delete();
                $request->delete();
            });

        $jobs = [
            [
                'suffix' => '001',
                'title' => 'Business Cards — ABC Ltd',
                'description' => 'Double-sided cards, brand colours, softcopy PDF required.',
                'priority' => ArtworkPriority::High,
                'status' => ArtworkRequestStatus::Requested,
                'designer' => null,
                'due' => now()->toDateString(),
                'version' => false,
            ],
            [
                'suffix' => '002',
                'title' => 'Product Labels — Kenya Tea',
                'description' => 'Roll labels, include barcode clear zone.',
                'priority' => ArtworkPriority::Urgent,
                'status' => ArtworkRequestStatus::Requested,
                'designer' => null,
                'due' => now()->toDateString(),
                'version' => false,
            ],
            [
                'suffix' => '003',
                'title' => 'Event Banner — Shell',
                'description' => '3×2 m banner, outdoor ink, PDF softcopy for print.',
                'priority' => ArtworkPriority::Normal,
                'status' => ArtworkRequestStatus::Requested,
                'designer' => null,
                'due' => now()->addDay()->toDateString(),
                'version' => false,
            ],
            [
                'suffix' => '004',
                'title' => 'Menu Cards — Safari Hotel',
                'description' => 'A5 trifold. Claim, design, upload final PDF, then mark complete.',
                'priority' => ArtworkPriority::High,
                'status' => ArtworkRequestStatus::InDesign,
                'designer' => $admin?->id,
                'due' => now()->toDateString(),
                'version' => false,
            ],
            [
                'suffix' => '005',
                'title' => 'Sticker Sheet — Coca Cola',
                'description' => 'Ready for PDF upload and submission.',
                'priority' => ArtworkPriority::Normal,
                'status' => ArtworkRequestStatus::InDesign,
                'designer' => $admin?->id ?? $designer?->id,
                'due' => now()->addDays(2)->toDateString(),
                'version' => false,
            ],
            [
                'suffix' => '006',
                'title' => 'Letterhead — Equity Demo',
                'description' => 'Client asked for logo size change.',
                'priority' => ArtworkPriority::High,
                'status' => ArtworkRequestStatus::RevisionRequested,
                'designer' => $designer?->id ?? $admin?->id,
                'due' => now()->subDay()->toDateString(),
                'version' => true,
                'revision' => true,
            ],
            [
                'suffix' => '007',
                'title' => 'Packaging Sleeve — Kevin WIP',
                'description' => 'Already claimed by Kevin — other designers should leave it.',
                'priority' => ArtworkPriority::Normal,
                'status' => ArtworkRequestStatus::InDesign,
                'designer' => $designer?->id,
                'due' => now()->addDays(3)->toDateString(),
                'version' => false,
            ],
        ];

        foreach ($jobs as $index => $job) {
            $customer = $customers[$index % $customers->count()];

            $artwork = ArtworkRequest::query()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'request_number' => $marker.'-'.$job['suffix'],
                'title' => $job['title'],
                'description' => $job['description'],
                'requested_by' => $sales->id,
                'assigned_designer_id' => $job['designer'],
                'priority' => $job['priority'],
                'status' => $job['status'],
                'due_date' => $job['due'],
                'current_version' => $job['version'] ? 1 : 0,
            ]);

            if ($job['version']) {
                $this->seedPdf($artwork, $job['designer'] ?? $sales->id);
            }

            if (! empty($job['revision'])) {
                ArtworkComment::query()->create([
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'artwork_request_id' => $artwork->id,
                    'user_id' => $sales->id,
                    'comment_type' => ArtworkCommentType::Customer,
                    'comment' => 'Please enlarge the logo and darken the brand green before re-uploading the PDF.',
                ]);
            }
        }

        $command?->info('Designer Desk demo jobs seeded (DESK-DEMO-001 … 007).');
        $command?->line('  • Unassigned jobs: claim from Available queue');
        $command?->line('  • Admin jobs: assigned to admin@janaprints.local');
        $command?->line('  • Designer WIP: assigned to designer@janaprints.local');
    }

    protected function seedPdf(ArtworkRequest $artwork, int $uploadedBy): void
    {
        $path = "artwork/{$artwork->company_id}/{$artwork->id}/versions/desk-demo-v1.pdf";
        Storage::disk('local')->put($path, "%PDF-1.4\n% Desk demo artwork\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n");

        ArtworkVersion::query()->create([
            'artwork_request_id' => $artwork->id,
            'version_number' => 1,
            'file_path' => $path,
            'original_name' => 'desk-demo-softcopy-v1.pdf',
            'mime_type' => 'application/pdf',
            'size' => Storage::disk('local')->size($path),
            'uploaded_by' => $uploadedBy,
            'notes' => 'Demo softcopy PDF',
        ]);
    }
}
