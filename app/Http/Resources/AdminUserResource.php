<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'avatar'            => $this->avatar,
            'avatar_url'        => $this->avatar_url,
            'role'              => $this->role,
            'is_super_admin'    => $this->isSuperAdmin(),
            'status'            => $this->status,
            'permissions'       => $this->permissions,
            'last_login_at'     => $this->last_login_at?->toDateTimeString(),
            'email_verified_at' => $this->email_verified_at?->toDateTimeString(),
            'created_at'        => $this->created_at?->toDateTimeString(),
            'updated_at'        => $this->updated_at?->toDateTimeString(),
        ];
    }
}
