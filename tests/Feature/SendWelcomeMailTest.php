<?php

namespace Tests\Feature;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use App\Models\UserEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendWelcomeMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_welcome_email_to_all_user_addresses(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'phone' => '+48123123123',
        ]);

        UserEmail::create(['user_id' => $user->id, 'email' => 'a@test.com', 'is_primary' => true]);
        UserEmail::create(['user_id' => $user->id, 'email' => 'b@test.com', 'is_primary' => false]);

        $this->postJson("/api/users/{$user->id}/send-welcome")
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
            ])
            ->assertJsonCount(2, 'sent_to');

        Mail::assertSent(WelcomeUserMail::class, 2);

        Mail::assertSent(WelcomeUserMail::class, function ($mail) {
            return $mail->hasTo('a@test.com');
        });

        Mail::assertSent(WelcomeUserMail::class, function ($mail) {
            return $mail->hasTo('b@test.com');
        });
    }
}
