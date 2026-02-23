<?php

namespace App\Actions;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmailToUser
{
    /**
     * @param User $user
     * @return array<string> lista adresów, na które wysłano
     */
    public function handle(User $user): array
    {
        $user->loadMissing('userEmailsRelation');

        $recipients = $user->userEmailsRelation
            ->pluck('email')
            ->map(fn ($e) => mb_strtolower(trim((string) $e)))
            ->filter()
            ->unique()
            ->values();

        foreach ($recipients as $email) {
            Mail::to($email)->send(new WelcomeUserMail($user));
        }

        return $recipients->all();
    }
}
