<x-admin-layout :title="$request->request_number" :breadcrumbs="[['label' => __('Artwork'), 'url' => route('admin.artwork.dashboard')], ['label' => $request->request_number]]">
    <x-admin.page-header :title="$request->request_number" :description="$request->customer?->company_name">
        <span class="erp-badge">{{ str_replace('_', ' ', $request->status->value) }}</span>
        <span class="text-sm text-slate-500">v{{ $request->current_version }}</span>
        @can('update', $request)
            <a href="{{ route('admin.artwork.edit', $request) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
        @endcan
    </x-admin.page-header>

    <x-admin.card class="mb-6">
        <h3 class="font-medium mb-3">{{ __('Workflow') }}</h3>
        <div class="flex flex-wrap gap-2">
            @can('assign', $request)
                <form method="POST" action="{{ route('admin.artwork.assign', $request) }}" class="flex flex-wrap gap-2 items-end">
                    @csrf
                    <select name="assigned_designer_id" class="erp-input" required>
                        <option value="">{{ __('Assign designer') }}</option>
                        @foreach (\App\Models\User::query()->where('company_id', $request->company_id)->where('is_active', true)->orderBy('name')->get() as $designer)
                            <option value="{{ $designer->id }}" @selected($request->assigned_designer_id === $designer->id)>{{ $designer->name }}</option>
                        @endforeach
                    </select>
                    <button class="erp-btn-secondary">{{ __('Assign') }}</button>
                </form>
            @endcan
            @can('submit', $request)
                <form method="POST" action="{{ route('admin.artwork.submit', $request) }}">@csrf
                    <button class="erp-btn-primary">{{ __('Submit for approval') }}</button></form>
            @endcan
            @can('startDesign', $request)
                <form method="POST" action="{{ route('admin.artwork.start-design', $request) }}">@csrf
                    <button class="erp-btn-secondary">{{ __('Resume design') }}</button></form>
            @endcan
            @can('approve', $request)
                <form method="POST" action="{{ route('admin.artwork.approve', $request) }}" class="flex flex-wrap gap-2 items-end">
                    @csrf
                    <select name="decision" class="erp-input" required>
                        <option value="approved">{{ __('Approve') }}</option>
                        <option value="revision_requested">{{ __('Request revision') }}</option>
                        <option value="rejected">{{ __('Reject') }}</option>
                    </select>
                    <input type="text" name="comments" class="erp-input" placeholder="{{ __('Comments') }}">
                    <button class="erp-btn-primary">{{ __('Record decision') }}</button>
                </form>
            @endcan
        </div>
    </x-admin.card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Details') }}</h3>
            <dl class="text-sm space-y-2">
                <div><dt class="text-slate-500">{{ __('Title') }}</dt><dd>{{ $request->title }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Priority') }}</dt><dd>{{ $request->priority->value }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Due') }}</dt><dd>{{ $request->due_date?->format('Y-m-d') ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Designer') }}</dt><dd>{{ $request->assignedDesigner?->name ?? '—' }}</dd></div>
                @if ($request->description)
                    <div><dt class="text-slate-500">{{ __('Description') }}</dt><dd>{{ $request->description }}</dd></div>
                @endif
            </dl>
        </x-admin.card>

        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Versions') }}</h3>
            @forelse ($request->versions as $version)
                <div class="text-sm border-b border-slate-100 py-2">
                    <strong>v{{ $version->version_number }}</strong> — {{ $version->original_name }}
                    <span class="text-slate-500">({{ $version->uploader?->name }})</span>
                    @if ($version->notes)<p class="text-slate-600">{{ $version->notes }}</p>@endif
                </div>
            @empty
                <p class="text-sm text-slate-500">{{ __('No versions uploaded yet.') }}</p>
            @endforelse
            @can('create', [App\Models\Artwork\ArtworkVersion::class, $request])
                <form method="POST" action="{{ route('admin.artwork.versions.store', $request) }}" enctype="multipart/form-data" data-turbo-frame="_top" class="mt-4 space-y-2">
                    @csrf
                    <input type="file" name="file" class="erp-input w-full" required>
                    <input type="text" name="notes" class="erp-input w-full" placeholder="{{ __('Version notes') }}">
                    <button class="erp-btn-secondary">{{ __('Upload version') }}</button>
                </form>
            @endcan
        </x-admin.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Reference files') }}</h3>
            @forelse ($request->files as $file)
                <div class="text-sm py-1">{{ $file->original_name }} ({{ $file->file_type->value }})</div>
            @empty
                <p class="text-sm text-slate-500">{{ __('No reference files.') }}</p>
            @endforelse
            @can('update', $request)
                <form method="POST" action="{{ route('admin.artwork.files.store', $request) }}" enctype="multipart/form-data" data-turbo-frame="_top" class="mt-4">
                    @csrf
                    <input type="file" name="file" class="erp-input w-full" required>
                    <button class="erp-btn-secondary mt-2">{{ __('Upload reference') }}</button>
                </form>
            @endcan
        </x-admin.card>

        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Comments & approvals') }}</h3>
            @foreach ($request->comments as $comment)
                <div class="text-sm border-b py-2">
                    <span class="erp-badge">{{ $comment->comment_type->value }}</span>
                    {{ $comment->user?->name }}: {{ $comment->comment }}
                </div>
            @endforeach
            @can('view', $request)
                <form method="POST" action="{{ route('admin.artwork.comments.store', $request) }}" class="mt-4 space-y-2">
                    @csrf
                    <select name="comment_type" class="erp-input w-full">
                        <option value="internal">{{ __('Internal') }}</option>
                        <option value="customer">{{ __('Customer') }}</option>
                    </select>
                    <textarea name="comment" class="erp-input w-full" rows="2" required></textarea>
                    <button class="erp-btn-secondary">{{ __('Add comment') }}</button>
                </form>
            @endcan
            @foreach ($request->approvals as $approval)
                <div class="text-sm mt-3 text-slate-600">
                    {{ $approval->decision->value }} — {{ $approval->approver?->name }}
                    @if ($approval->comments) ({{ $approval->comments }}) @endif
                </div>
            @endforeach
        </x-admin.card>
    </div>
</x-admin-layout>
