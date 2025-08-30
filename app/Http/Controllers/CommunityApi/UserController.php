<?php

namespace App\Http\Controllers\CommunityApi;

use App\Data\LoginData;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Login
     *
     * @bodyParam email string required The email of the user.
     * @bodyParam password string required The password of the user.
     * @bodyParam device_id string required The device id of the user.
     *
     * @unauthenticated
     */
    public function login(LoginData $data)
    {
        $user = User::whereRaw('LOWER(email) = ?', [strtolower($data->email)])->first();

        if (! $user || ! Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return [
            'token' => $user->createToken($data->device_id)->plainTextToken,
        ];
    }

    /**
     * Get authenticated user info
     */
    public function me()
    {
        return new UserResource(auth()->user());
    }
}
