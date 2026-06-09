<x-admin-layout :title="$application->candidate->full_name">
    <x-admin.page-header :title="$application->candidate->full_name" :description="$application->reference">
        <x-slot name="actions">
            <span class="erp-badge bg-slate-100 text-slate-700">{{ $application->stage->label() }}</span>
            @if ($application->employee)
                <a href="{{ route('admin.hr.employees.show', $application->employee) }}" class="erp-btn-secondary text-xs">{{ __('View Employee') }}</a>
            @endif
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <x-admin.kpi-widget :label="__('Vacancy')" :value="$application->vacancy->title" icon="briefcase" />
        <x-admin.kpi-widget :label="__('Email')" :value="$application->candidate->email ?? '—'" icon="mail" />
        <x-admin.kpi-widget :label="__('Applied')" :value="$application->applied_at->format('M j, Y')" icon="calendar" />
    </div>

    @can('update', $application)
        <div class="mb-6 flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.hr.recruitment.applications.advance', $application) }}" class="flex items-center gap-2">
                @csrf
                <select name="stage" class="erp-input text-xs">
                    @foreach ($stages as $stage)
                        <option value="{{ $stage->value }}" @selected($application->stage === $stage)>{{ $stage->label() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="erp-btn-secondary text-xs">{{ __('Move') }}</button>
            </form>
            @if ($application->stage !== \App\Enums\RecruitmentPipelineStage::Rejected)
                <form method="POST" action="{{ route('admin.hr.recruitment.applications.reject', $application) }}">@csrf<button type="submit" class="erp-btn-secondary text-xs">{{ __('Reject') }}</button></form>
            @endif
            @if ($application->stage === \App\Enums\RecruitmentPipelineStage::Accepted && ! $application->onboarding)
                <form method="POST" action="{{ route('admin.hr.recruitment.onboarding.start', $application) }}">@csrf<button type="submit" class="erp-btn-primary text-xs">{{ __('Start Onboarding') }}</button></form>
            @endif
        </div>
    @endcan

    <div class="grid gap-6 lg:grid-cols-2">
        @can('update', $application)
            <x-admin.card :title="__('Interview Scheduling')">
                <form method="POST" action="{{ route('admin.hr.recruitment.applications.interview', $application) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="erp-label" for="scheduled_at">{{ __('Date & Time') }}</label>
                        <input type="datetime-local" id="scheduled_at" name="scheduled_at" class="erp-input w-full" required>
                    </div>
                    <div>
                        <label class="erp-label" for="location">{{ __('Location') }}</label>
                        <input type="text" id="location" name="location" class="erp-input w-full">
                    </div>
                    <button type="submit" class="erp-btn-primary text-xs">{{ __('Schedule Interview') }}</button>
                </form>
                @if ($application->interviews->isNotEmpty())
                    <div class="mt-4 space-y-2 text-sm">
                        @foreach ($application->interviews as $interview)
                            <div class="rounded border border-erp-border/60 p-2">
                                <p class="font-medium">{{ $interview->scheduled_at->format('M j, Y H:i') }}</p>
                                <p class="text-slate-500">{{ $interview->status->label() }} · {{ $interview->location ?? __('TBD') }}</p>
                                @if ($interview->feedback)
                                    <p class="mt-1 text-xs">{{ __('Rating') }}: {{ $interview->feedback->rating }}/5 — {{ $interview->feedback->recommendation->label() }}</p>
                                @elseif ($interview->status === \App\Enums\InterviewScheduleStatus::Scheduled)
                                    <form method="POST" action="{{ route('admin.hr.recruitment.applications.feedback', $application) }}" class="mt-2 space-y-2">
                                        @csrf
                                        <input type="hidden" name="interview_schedule_id" value="{{ $interview->id }}">
                                        <input type="number" name="rating" min="1" max="5" class="erp-input w-20 text-xs" placeholder="{{ __('Rating') }}" required>
                                        <select name="recommendation" class="erp-input text-xs">
                                            @foreach ($recommendations as $rec)
                                                <option value="{{ $rec->value }}">{{ $rec->label() }}</option>
                                            @endforeach
                                        </select>
                                        <textarea name="feedback" rows="2" class="erp-input w-full text-xs" placeholder="{{ __('Feedback') }}"></textarea>
                                        <button type="submit" class="erp-btn-secondary text-xs">{{ __('Submit Feedback') }}</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-admin.card>

            <x-admin.card :title="__('Offer Letter')">
                <form method="POST" action="{{ route('admin.hr.recruitment.applications.offer', $application) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="erp-label" for="salary_offered">{{ __('Salary Offered') }}</label>
                        <input type="number" step="0.01" id="salary_offered" name="salary_offered" class="erp-input w-full">
                    </div>
                    <div>
                        <label class="erp-label" for="start_date">{{ __('Start Date') }}</label>
                        <input type="date" id="start_date" name="start_date" class="erp-input w-full">
                    </div>
                    <div>
                        <label class="erp-label" for="terms">{{ __('Terms') }}</label>
                        <textarea id="terms" name="terms" rows="2" class="erp-input w-full"></textarea>
                    </div>
                    <button type="submit" class="erp-btn-primary text-xs">{{ __('Create Offer') }}</button>
                </form>
                @foreach ($application->offerLetters as $offer)
                    <div class="mt-4 rounded border border-erp-border/60 p-3 text-sm">
                        <p class="font-medium">{{ $offer->reference }} — {{ $offer->status->label() }}</p>
                        <p class="text-slate-500">{{ $offer->salary_offered ? number_format($offer->salary_offered, 2) : '—' }} · {{ $offer->start_date?->format('Y-m-d') ?? '—' }}</p>
                        <div class="mt-2 flex gap-2">
                            @if ($offer->status === \App\Enums\OfferLetterStatus::Draft)
                                <form method="POST" action="{{ route('admin.hr.recruitment.offers.send', $offer) }}">@csrf<button type="submit" class="erp-btn-secondary text-xs">{{ __('Send') }}</button></form>
                            @endif
                            @if ($offer->status === \App\Enums\OfferLetterStatus::Sent)
                                <form method="POST" action="{{ route('admin.hr.recruitment.offers.accept', $offer) }}">@csrf<button type="submit" class="erp-btn-primary text-xs">{{ __('Accept') }}</button></form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </x-admin.card>
        @endcan
    </div>

    @if ($application->onboarding)
        <x-admin.card class="mt-6" :title="__('Onboarding')">
            <p class="text-sm">{{ __('Status') }}: {{ $application->onboarding->status->label() }}</p>
            <a href="{{ route('admin.hr.recruitment.onboarding.show', $application->onboarding) }}" class="erp-btn-secondary mt-3 inline-block text-xs">{{ __('View onboarding') }}</a>
        </x-admin.card>
    @endif
</x-admin-layout>
