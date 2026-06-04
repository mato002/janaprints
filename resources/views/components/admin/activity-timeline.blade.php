@props(['items'])

<ul class="space-y-4" role="list">
    @forelse ($items as $log)
        @php
            $userName = is_array($log) ? ($log['user_name'] ?? null) : ($log->user?->name ?? null);
            $message = is_array($log) ? ($log['message'] ?? null) : null;
            $action = is_array($log) ? ($log['action'] ?? '') : ($log->action ?? '');
            $modelType = is_array($log) ? ($log['model_type'] ?? null) : ($log->model_type ?? null);
            $modelId = is_array($log) ? ($log['model_id'] ?? null) : ($log->model_id ?? null);
            $createdAt = is_array($log) ? ($log['created_at'] ?? null) : ($log->created_at ?? null);
            $ipAddress = is_array($log) ? ($log['ip_address'] ?? null) : ($log->ip_address ?? null);
        @endphp
        <li class="relative flex gap-4 pl-6">
            <span class="absolute left-0 top-1.5 flex h-3 w-3 items-center justify-center">
                <span class="h-2 w-2 rounded-full bg-erp-accent ring-4 ring-erp-accent/10"></span>
            </span>
            @if (! $loop->last)
                <span class="absolute left-[5px] top-4 h-full w-px bg-erp-border" aria-hidden="true"></span>
            @endif
            <div class="min-w-0 flex-1 pb-1">
                <p class="text-sm text-erp-primary">
                    @if ($message)
                        {{ $message }}
                    @else
                        <span class="font-medium">{{ $userName ?? __('System') }}</span>
                        <span class="text-slate-500">{{ $action }}</span>
                        @if ($modelType)
                            <span class="text-slate-400">{{ class_basename($modelType) }} #{{ $modelId }}</span>
                        @endif
                    @endif
                </p>
                <p class="mt-0.5 text-xs text-slate-400">
                    @if ($createdAt)
                        @php
                            $timestamp = $createdAt instanceof \DateTimeInterface
                                ? $createdAt
                                : (is_string($createdAt) || is_numeric($createdAt) ? $createdAt : null);
                        @endphp
                        @if ($timestamp !== null)
                            {{ \Illuminate\Support\Carbon::parse($timestamp)->diffForHumans() }}
                        @endif
                    @endif
                    @if ($ipAddress)
                        · {{ $ipAddress }}
                    @endif
                </p>
            </div>
        </li>
    @empty
        <li class="py-8 text-center text-sm text-slate-500">{{ __('No recent activity.') }}</li>
    @endforelse
</ul>
