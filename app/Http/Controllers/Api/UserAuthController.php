<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserAuthRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserAuthController extends Controller
{
    // POST /api/user/register
    public function register(UserAuthRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars/users', 'public');
        }

        $data['password'] = Hash::make($data['password']);
        $data['status']   = 'active';

        $user  = User::create($data);
        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
            'token'   => $token,
            'user'    => new UserResource($user),
        ], 201);
    }

    // POST /api/user/login
    public function login(UserAuthRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if ($user->status === 'banned') {
            return response()->json(['success' => false, 'message' => 'Your account has been banned.'], 403);
        }

        if ($user->status === 'inactive') {
            return response()->json(['success' => false, 'message' => 'Your account is inactive.'], 403);
        }

        $user->update(['last_login_at' => now()]);
        $user->tokens()->delete();
        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => new UserResource($user),
        ]);
    }

    // GET /api/user/profile
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user'    => new UserResource($request->user()),
        ]);
    }

    // PUT /api/user/profile
    public function updateProfile(UserAuthRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars/users', 'public');
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user'    => new UserResource($user->fresh()),
        ]);
    }

    // POST /api/user/logout
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    // GET /api/user/all
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $users = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => UserResource::collection($users),
            'meta'    => [
                'total'        => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
            ],
        ]);
    }

    // GET /api/user/{id}
    public function show(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'user'    => new UserResource($user),
        ]);
    }

    // DELETE /api/user/{id}
    public function destroy(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
    }
}
