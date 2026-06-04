<?php

namespace App\Services\Dispatch;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Models\Dispatch\DeliveryNote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DeliveryCalendarService
{
    /**
     * @return array{
     *     month: string,
     *     label: string,
     *     prev_month: string,
     *     next_month: string,
     *     weeks: list<list<array{
     *         date: string,
     *         label: string,
     *         in_month: bool,
     *         is_today: bool,
     *         notes: list<array{id: int, number: string, customer: string, status: string, status_class: string}>
     *     }>>,
     *     status_counts: array<string, int>
     * }
     */
    public function calendarMonth(Request $request): array
    {
        $month = $this->resolveMonth($request->query('month'));
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $gridStart = $start->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $end->copy()->endOfWeek(Carbon::MONDAY);

        $notes = DeliveryNote::query()
            ->forTenant()
            ->with(['customer:id,company_name'])
            ->whereBetween('delivery_date', [$start->toDateString(), $end->toDateString()])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('delivery_date')
            ->orderBy('delivery_note_number')
            ->get();

        $weeks = [];
        $cursor = $gridStart->copy();

        while ($cursor->lte($gridEnd)) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $date = $cursor->copy();
                $week[] = [
                    'date' => $date->toDateString(),
                    'label' => $date->format('j'),
                    'in_month' => $date->month === $start->month,
                    'is_today' => $date->isToday(),
                    'notes' => $this->notesForDay($notes, $date),
                ];
                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        return [
            'month' => $month->format('Y-m'),
            'label' => $month->format('F Y'),
            'prev_month' => $month->copy()->subMonth()->format('Y-m'),
            'next_month' => $month->copy()->addMonth()->format('Y-m'),
            'weeks' => $weeks,
            'status_counts' => $this->statusCounts($notes),
        ];
    }

    /**
     * @param  Collection<int, DeliveryNote>  $notes
     * @return list<array{id: int, number: string, customer: string, status: string, status_class: string}>
     */
    protected function notesForDay(Collection $notes, Carbon $day): array
    {
        $date = $day->toDateString();
        $items = [];

        foreach ($notes as $note) {
            if ($note->delivery_date->toDateString() !== $date) {
                continue;
            }

            $items[] = [
                'id' => $note->id,
                'number' => $note->delivery_note_number,
                'customer' => $note->customer?->company_name ?? '—',
                'status' => $note->status->value,
                'status_class' => $this->statusClass($note->status),
            ];
        }

        return $items;
    }

    /**
     * @param  Collection<int, DeliveryNote>  $notes
     * @return array<string, int>
     */
    protected function statusCounts(Collection $notes): array
    {
        $counts = [];

        foreach (DeliveryNoteStatus::cases() as $status) {
            $counts[$status->value] = $notes->where('status', $status)->count();
        }

        return $counts;
    }

    protected function statusClass(DeliveryNoteStatus $status): string
    {
        return match ($status) {
            DeliveryNoteStatus::Draft => 'bg-amber-50 text-amber-900',
            DeliveryNoteStatus::Dispatched => 'bg-sky-50 text-sky-900',
            DeliveryNoteStatus::Delivered => 'bg-emerald-50 text-emerald-900',
            DeliveryNoteStatus::Cancelled => 'bg-slate-100 text-slate-500 line-through',
        };
    }

    protected function resolveMonth(?string $month): Carbon
    {
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        }

        return now()->startOfMonth();
    }
}
