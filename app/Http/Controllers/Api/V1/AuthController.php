<?php

namespace App\Http\Controllers\Api\V1;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    /**
     * Authenticate user and return tokens.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', 'Validation failed', $validator->errors(), 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('UNAUTHORIZED', 'Invalid username or password', null, 401);
        }

        if (!$user->allow_login) {
            return $this->error('FORBIDDEN', 'Login not allowed for this account', null, 403);
        }

        $tokenResult = $user->createToken('InventoryOS Personal Access Token');
        $token = $tokenResult->accessToken;

        return $this->success([
            'accessToken' => $token,
            'refreshToken' => null, // Passport Personal Access tokens don't have refresh tokens by default here
            'expiresIn' => 3600 * 24, // Example expiration
            'user' => $this->formatUser($user)
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request)
    {
        $user = $request->user();
        return $this->success($this->formatUser($user));
    }

    /**
     * Format user object according to spec.
     */
    private function formatUser($user)
    {
        $role = $user->roles->first()->name ?? 'N/A';
        // Strip business ID suffix (e.g., "Admin#2" -> "Admin")
        $role = explode('#', $role)[0];

        $permitted_locations = $user->permitted_locations();
        $query = \App\BusinessLocation::where('business_id', $user->business_id)->Active();
        if ($permitted_locations != 'all') {
            $query->whereIn('id', $permitted_locations);
        }
        
        $locations = $query->select('id', 'name')->get()->map(function($location) {
            return [
                'id' => (int)$location->id,
                'name' => $location->name
            ];
        });

        return [
            'id' => (int)$user->id,
            'name' => trim($user->first_name . ' ' . $user->last_name),
            'email' => $user->email,
            'role' => $role,
            'branchId' => $user->business_id, // Mapping business as branch for now
            'branchName' => $user->business->name ?? 'Main Branch',
            'isActive' => (bool)$user->allow_login,
            'twoFactorEnabled' => false, // Placeholder
            'autoLockSeconds' => 1800,
            'permittedLocations' => $locations
        ];
    }
}
