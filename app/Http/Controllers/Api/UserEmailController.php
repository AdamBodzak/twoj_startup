<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserEmailRequest;
use App\Http\Requests\UpdateUserEmailRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserEmailController extends Controller
{
    public function store(StoreUserEmailRequest $request, User $user)
    {
        $data = $request->validated();

        \DB::transaction(function () use ($user, $data) {
            // jeśli dodawany email ma być primary -> zdejmij primary z innych
            if (($data['is_primary'] ?? false) === true) {
                $user->UserEmailController()->update(['is_primary' => false]);
            }

            $user->userEmailsRelation()->create([
                'email' => mb_strtolower(trim($data['email'])),
                'is_primary' => (bool) ($data['is_primary'] ?? false),
            ]);

            // jeśli user nie ma żadnego primary (np. pierwszy email) -> ustaw
            if ($user->userEmailsRelation()->where('is_primary', true)->count() === 0) {
                $user
                    ->userEmailsRelation()
                    ->oldest('id')
                    ->first()
                    ?->update(['is_primary' => true]);
            }
        });

        return new UserResource($user->refresh()->load('userEmailsRelation'));
    }

    public function update(UpdateUserEmailRequest $request, User $user, UserEmail $email)
    {
        // zabezpieczenie: email musi należeć do usera
        abort_unless($email->user_id === $user->id, 404);

        $data = $request->validated();

        \DB::transaction(function () use ($user, $email, $data) {
            if (array_key_exists('email', $data)) {
                $email->email = mb_strtolower(trim($data['email']));
            }

            if (array_key_exists('is_primary', $data)) {
                if ($data['is_primary'] === true) {
                    $user->userEmailsRelation()->update(['is_primary' => false]);
                    $email->is_primary = true;
                } else {
                    // pozwalamy zdjąć primary, ale pilnujemy by zawsze było jakieś primary
                    $email->is_primary = false;
                }
            }

            $email->save();

            // gwarancja: zawsze jest 1 primary (jeśli są emaile)
            if ($user->userEmailsRelation()->exists() && $user->userEmailsRelation()->where('is_primary', true)->count() === 0) {
                $user
                    ->userEmailsRelation()
                    ->oldest('id')
                    ->first()
                    ?->update(['is_primary' => true]);
            }
        });

        return new UserResource($user->refresh()->load('userEmailsRelation'));
    }

    public function destroy(User $user, int $email)
    {
        $emailModel = $user->userEmailsRelation()
            ->where('id', $email)
            ->first();

        if (! $emailModel) {
            return response()->json([
                'message' => 'Email not found for this user.'
            ], 404);
        }

        \DB::transaction(function () use ($user, $emailModel) {

            $wasPrimary = $emailModel->is_primary;

            $emailModel->delete();

            if ($wasPrimary) {
                $user->userEmailsRelation()
                    ->oldest('id')
                    ->first()
                    ?->update(['is_primary' => true]);
            }
        });

        return response()->json([
            'message' => 'Email deleted successfully.'
        ]);
    }
}
