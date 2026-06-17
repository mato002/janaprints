<?php

namespace App\Http\Controllers\Ess;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ess\Concerns\ResolvesEmployee;
use App\Support\Ess\EssAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EssProfileController extends Controller
{
    use ResolvesEmployee;

    public function update(Request $request, EssAuditService $audit): RedirectResponse
    {
        $employee = $this->essEmployee();
        $user = $this->essUser();

        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'next_of_kin_name' => ['nullable', 'string', 'max:255'],
            'next_of_kin_phone' => ['nullable', 'string', 'max:50'],
            'next_of_kin_relationship' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $changes = [];
        foreach (array_keys($validated) as $field) {
            if ($field === 'photo') {
                continue;
            }

            $newValue = $validated[$field] ?? null;
            $oldValue = $employee->{$field};

            if ((string) ($oldValue ?? '') !== (string) ($newValue ?? '')) {
                $changes[$field] = ['from' => $oldValue, 'to' => $newValue];
            }
        }

        if ($request->hasFile('photo')) {
            if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
                Storage::disk('public')->delete($employee->photo);
            }

            $path = $request->file('photo')->store("employee-photos/{$employee->company_id}", 'public');
            $changes['photo'] = ['from' => $employee->photo, 'to' => $path];
            $validated['photo'] = $path;
        } else {
            unset($validated['photo']);
        }

        $employee->update($validated);

        if ($changes !== []) {
            $audit->logProfileUpdated($employee, $user, $changes);
        }

        return redirect()
            ->route('ess.dashboard', ['tab' => 'profile'])
            ->with('status', __('Profile updated successfully.'));
    }
}
