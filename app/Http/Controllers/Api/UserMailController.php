<?php

namespace App\Http\Controllers\Api;

use App\Actions\SendWelcomeEmailToUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserMailController extends Controller
{
    public function sendWelcome(User $user, SendWelcomeEmailToUser $action)
    {
        $sentTo = $action->handle($user);

        return response()->json([
            'status' => 'ok',
            'sent_to' => $sentTo,
            'message' => 'Witamy użytkownika ' . $user->first_name,
        ]);
    }}
