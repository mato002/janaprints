<?php

namespace App\Services\Client;

use App\Models\Crm\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportException;

class ClientPortalInvitationService
{
    /**
     * @throws ValidationException
     * @throws TransportException
     */
    public function invite(Customer $customer): User
    {
        $email = strtolower(trim((string) $customer->email));

        if ($email === '') {
            throw ValidationException::withMessages([
                'customer' => __('Add an email address to this customer before inviting them to the client portal.'),
            ]);
        }

        $user = DB::transaction(function () use ($customer, $email): User {
            $existingUser = User::query()->where('email', $email)->first();

            if ($existingUser?->isStaffAccount()) {
                throw ValidationException::withMessages([
                    'customer' => __('That email address belongs to a staff account and cannot be used for the client portal.'),
                ]);
            }

            if ($existingUser && $existingUser->customer_id !== null && (int) $existingUser->customer_id !== (int) $customer->id) {
                throw ValidationException::withMessages([
                    'customer' => __('That email address is already linked to another customer portal account.'),
                ]);
            }

            $name = filled($customer->contact_person)
                ? $customer->contact_person
                : ($customer->company_name ?: $email);

            if ($existingUser) {
                $existingUser->fill([
                    'name' => $name,
                    'company_id' => $customer->company_id,
                    'default_branch_id' => $customer->branch_id,
                    'customer_id' => $customer->id,
                    'employee_id' => null,
                    'is_active' => true,
                    'email_verified_at' => $existingUser->email_verified_at ?? now(),
                ])->save();

                return $existingUser->fresh();
            }

            return User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => Str::password(32),
                'company_id' => $customer->company_id,
                'default_branch_id' => $customer->branch_id,
                'customer_id' => $customer->id,
                'employee_id' => null,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
        });

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            Password::broker()->getRepository()->delete($user);

            throw ValidationException::withMessages([
                'customer' => __($status),
            ]);
        }

        return $user;
    }
}
