<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 100)); // limit 1..100

        $users = User::query()
            ->with('userEmailsRelation') // eager loading relacji
            ->orderByDesc('id')
            ->paginate($perPage);

        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreUserRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        $user = \DB::transaction(function () use ($data) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'phone'      => $data['phone'],
            ]);

            $emails = collect($data['emails'])
                ->map(function (array $row) {
                    return [
                        'email' => mb_strtolower(trim($row['email'])),
                        'is_primary' => (bool) ($row['is_primary'] ?? false),
                    ];
                });

            // jeśli żaden nie jest primary -> pierwszy ustaw primary
            if ($emails->where('is_primary', true)->count() === 0) {
                $emails = $emails->values();
                $emails[0]['is_primary'] = true;
            }

            $user->userEmailsRelation()->createMany($emails->toArray());

            return $user->load('userEmailsRelation');
        });

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        return new UserResource($user->load('userEmailsRelation'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
