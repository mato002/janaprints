<?php

namespace Tests\Feature\Auth;

use App\Mail\Auth\PasswordResetMail;
use App\Models\Crm\Customer;
use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $this->get('/forgot-password')->assertOk();
        $this->get('/client/forgot-password')->assertOk();
    }

    public function test_reset_password_link_can_be_requested_for_staff(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_password_link_can_be_requested_for_clients(): void
    {
        Notification::fake();

        $customer = Customer::factory()->create();
        $user = User::factory()->create([
            'company_id' => $customer->company_id,
            'customer_id' => $customer->id,
            'employee_id' => null,
        ]);

        $this->post('/client/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
            $this->get('/reset-password/'.$notification->token.'?email='.urlencode($notification->notifiable->email))
                ->assertOk();

            return true;
        });
    }

    public function test_staff_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.login'));

            return true;
        });

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }

    public function test_client_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $customer = Customer::factory()->create();
        $user = User::factory()->create([
            'company_id' => $customer->company_id,
            'customer_id' => $customer->id,
            'employee_id' => null,
        ]);

        $this->post('/client/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $response = $this->post('/client/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-client-password',
                'password_confirmation' => 'new-client-password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('client.login'));

            return true;
        });

        $this->assertTrue(Hash::check('new-client-password', $user->fresh()->password));
    }

    public function test_client_reset_email_links_to_client_reset_route(): void
    {
        Notification::fake();

        config(['mailboxes.system.noreply' => 'noreply@janaprints.co.ke']);

        $customer = Customer::factory()->create();
        $user = User::factory()->create([
            'company_id' => $customer->company_id,
            'customer_id' => $customer->id,
            'employee_id' => null,
        ]);

        $this->post('/client/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $payload = $notification->toCorporateMail($user);

            $this->assertSame('password_reset', $payload['sender_purpose']);
            $this->assertSame($user->email, $payload['to'][0]['email']);
            $this->assertStringContainsString('/client/reset-password/', $payload['body']);

            $mail = $notification->toMail($user);
            $this->assertInstanceOf(PasswordResetMail::class, $mail);
            $this->assertSame($user->email, $mail->to[0]['address'] ?? null);
            $this->assertStringContainsString('noreply@', strtolower($mail->fromAddress));

            return true;
        });
    }
}
