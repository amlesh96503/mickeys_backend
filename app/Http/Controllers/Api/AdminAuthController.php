<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAuthRequest;
use App\Http\Resources\AdminUserResource;
use App\Models\AdminUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminAuthController extends Controller
{
    // POST /api/admin/register
    public function register(AdminAuthRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars/admins', 'public');
        }

        $data['password'] = Hash::make($data['password']);
        $data['role']     = $data['role'] ?? 'admin';
        $data['status']   = 'active';

        $admin = AdminUser::create($data);
        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Admin registered successfully.',
            'token'   => $token,
            'admin'   => new AdminUserResource($admin),
        ], 201);
    }

    // POST /api/admin/login
    public function login(AdminAuthRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $admin = AdminUser::where('email', $credentials['email'])->first();

        if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials.'], 401);
        }

        if ($admin->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => "Your admin account is {$admin->status}.",
            ], 403);
        }

        $admin->update(['last_login_at' => now()]);
        $admin->tokens()->delete();
        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'admin'   => new AdminUserResource($admin),
        ]);
    }

    // GET /api/admin/profile
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'admin'   => new AdminUserResource($request->user()),
        ]);
    }

    // PUT /api/admin/profile
    public function updateProfile(AdminAuthRequest $request): JsonResponse
    {
        $admin = $request->user();
        $data  = $request->only(['name', 'phone']);

        if ($request->hasFile('avatar')) {
            if ($admin->avatar && Storage::disk('public')->exists($admin->avatar)) {
                Storage::disk('public')->delete($admin->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars/admins', 'public');
        }

        $admin->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'admin'   => new AdminUserResource($admin->fresh()),
        ]);
    }

    // POST /api/admin/logout
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
    }

    // GET /api/admin/all
    public function index(Request $request): JsonResponse
    {
        $query = AdminUser::query();

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('role'))   $query->where('role', $request->role);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $admins = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => AdminUserResource::collection($admins),
            'meta'    => [
                'total'        => $admins->total(),
                'current_page' => $admins->currentPage(),
                'last_page'    => $admins->lastPage(),
                'per_page'     => $admins->perPage(),
            ],
        ]);
    }

    // GET /api/admin/{id}
    public function show(int $id): JsonResponse
    {
        $admin = AdminUser::find($id);

        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Admin not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'admin'   => new AdminUserResource($admin),
        ]);
    }

    // PUT /api/admin/{id}  (Only super_admin)
    public function update(AdminAuthRequest $request, int $id): JsonResponse
    {
        $currentAdmin = $request->user();
        $admin = AdminUser::find($id);

        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Admin not found.'], 404);
        }

        // Only super_admin can update others (ya khud ko)
        if (!$currentAdmin->isSuperAdmin() && $currentAdmin->id !== $admin->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $data = $request->validated();

        // Sirf super_admin role change kar sakta hai
        if (isset($data['role']) && !$currentAdmin->isSuperAdmin()) {
            unset($data['role']);
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($admin->avatar && Storage::disk('public')->exists($admin->avatar)) {
                Storage::disk('public')->delete($admin->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars/admins', 'public');
        }

        $admin->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Admin updated successfully.',
            'admin'   => new AdminUserResource($admin->fresh()),
        ]);
    }

    // DELETE /api/admin/{id}  (Only super_admin)
    public function destroy(Request $request, int $id): JsonResponse
    {
        $currentAdmin = $request->user();
        $admin = AdminUser::find($id);

        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Admin not found.'], 404);
        }

        if (!$currentAdmin->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only super admin can delete admins.'], 403);
        }

        if ($currentAdmin->id === $admin->id) {
            return response()->json(['success' => false, 'message' => 'You cannot delete yourself.'], 403);
        }

        if ($admin->avatar && Storage::disk('public')->exists($admin->avatar)) {
            Storage::disk('public')->delete($admin->avatar);
        }

        $admin->tokens()->delete();
        $admin->delete();

        return response()->json(['success' => true, 'message' => 'Admin deleted successfully.']);
    }
}
